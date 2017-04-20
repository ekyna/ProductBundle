<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\ORM\PersistentCollection;
use Ekyna\Bundle\ProductBundle\Entity\SpecialOffer;
use Ekyna\Bundle\ProductBundle\Event\SpecialOfferEvents;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SpecialOfferListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferListener implements EventSubscriberInterface
{
    protected const FIELDS = ['product', 'percent', 'minQuantity', 'startsAt', 'endsAt', 'stack', 'enabled'];

    protected PersistenceHelperInterface $persistenceHelper;
    protected OfferInvalidator $offerInvalidator;
    protected PriceInvalidator $priceInvalidator;
    protected TranslatorInterface $translator;

    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        OfferInvalidator $offerInvalidator,
        PriceInvalidator $priceInvalidator,
        TranslatorInterface $translator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator  = $offerInvalidator;
        $this->priceInvalidator  = $priceInvalidator;
        $this->translator        = $translator;
    }

    public function onInsert(ResourceEventInterface $event): SpecialOfferInterface
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->simplify($specialOffer, $event);

        $this->buildName($specialOffer);

        $this->offerInvalidator->invalidateSpecialOffer($specialOffer);

        return $specialOffer;
    }

    public function onUpdate(ResourceEventInterface $event): SpecialOfferInterface
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->simplify($specialOffer, $event);

        $this->buildName($specialOffer);

        $this->invalidate($specialOffer);

        // Special offer change(s)
        if ($this->specialOfferHasChanged($specialOffer)) {
            $this->offerInvalidator->invalidateSpecialOffer($specialOffer);
        }

        return $specialOffer;
    }

    public function onDelete(ResourceEventInterface $event): SpecialOfferInterface
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->offerInvalidator->invalidateSpecialOffer($specialOffer);

        // As offers are deleted by DBMS (On delete FK constraint),
        // we need to invalidate product prices here.
        $this->priceInvalidator->invalidateSpecialOffer($specialOffer);

        $this->invalidate($specialOffer, true);

        return $specialOffer;
    }

    protected function invalidate(SpecialOfferInterface $specialOffer, bool $andPrices = false): void
    {
        $productCs = $this->persistenceHelper->getChangeSet($specialOffer, 'product');

        // Product
        if (!empty($productCs)) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
            foreach ([0, 1] as $key) {
                if (($product = $productCs[$key]) && ($id = $product->getId())) {
                    $this->offerInvalidator->invalidateByProductId($id);
                    if ($andPrices) {
                        $this->offerInvalidator->invalidateByProductId($id);
                    }
                }
            }
        }

        // Products association changes
        foreach ($specialOffer->getInsertedIds(SpecialOffer::REL_PRODUCTS) as $id) {
            $this->offerInvalidator->invalidateByProductId($id);
            if ($andPrices) {
                $this->offerInvalidator->invalidateByProductId($id);
            }
        }
        foreach ($specialOffer->getRemovedIds(SpecialOffer::REL_PRODUCTS) as $id) {
            $this->offerInvalidator->invalidateByProductId($id);
            if ($andPrices) {
                $this->offerInvalidator->invalidateByProductId($id);
            }
        }

        // Brands association changes
        foreach ($specialOffer->getInsertedIds(SpecialOffer::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
            if ($andPrices) {
                $this->offerInvalidator->invalidateByBrandId($id);
            }
        }
        foreach ($specialOffer->getRemovedIds(SpecialOffer::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
            if ($andPrices) {
                $this->offerInvalidator->invalidateByBrandId($id);
            }
        }
    }

    /**
     * Simplifies the special offer by removing products covered by brands.
     *
     * @param SpecialOfferInterface  $specialOffer
     * @param ResourceEventInterface $event
     */
    protected function simplify(SpecialOfferInterface $specialOffer, ResourceEventInterface $event)
    {
        if (null !== $specialOffer->getProduct()) {
            // Brands and products lists should be empty.
            return;
        }

        $brandIds = [];

        foreach ($specialOffer->getBrands() as $brand) {
            $brandIds[] = $brand->getId();
        }

        $messages = [];

        foreach ($specialOffer->getProducts() as $product) {
            $brand = $product->getBrand();
            if (in_array($brand->getId(), $brandIds)) {
                $specialOffer->removeProduct($product);

                $messages[] = $this->translator->trans('special_offer.message.product_removed', [
                    '{product}' => $product->getFullDesignation(),
                    '{brand}'   => $brand->getName(),
                ], 'EkynaProduct');
            }
        }

        if (!empty($messages)) {
            $event->addMessage(ResourceMessage::create(implode('<br>', $messages)));
        }
    }

    /**
     * Builds the special offer name.
     */
    protected function buildName(SpecialOfferInterface $specialOffer): void
    {
        if (!empty($specialOffer->getName())) {
            return;
        }

        $parts = ['-' . $specialOffer->getPercent()->toFixed(2) . '%'];

        if (null !== $product = $specialOffer->getProduct()) {
            if (32 > strlen($designation = $product->getDesignation())) {
                $parts[] = $designation;
            } else {
                $parts[] = substr($designation, 0, 32) . '...';
            }
        } elseif (empty($brands = $specialOffer->getBrands()->toArray())) {
            $parts[] = $specialOffer->getProducts()->count() . ' product(s)';
        } else {
            $parts[] = implode('/', array_map(function (BrandInterface $brand) {
                return $brand->getName();
            }, $brands));
        }

        if (!empty($groups = $specialOffer->getGroups()->toArray())) {
            $parts[] = implode('/', array_map(function (CustomerGroupInterface $group) {
                return $group->getName();
            }, $groups));
        }

        if (!empty($countries = $specialOffer->getCountries()->toArray())) {
            $parts[] = implode('/', array_map(function (CountryInterface $country) {
                return $country->getName();
            }, $countries));
        }

        $specialOffer->setName(implode(' - ', $parts));

        $this->persistenceHelper->persistAndRecompute($specialOffer, false);
    }

    /**
     * Returns whether the given special offer has changed.
     */
    protected function specialOfferHasChanged(SpecialOfferInterface $specialOffer): bool
    {
        if ($this->persistenceHelper->isChanged($specialOffer, static::FIELDS)) {
            return true;
        }

        // TODO use track association trait methods ?
        $groups = $specialOffer->getGroups();
        if ($groups instanceof PersistentCollection && $groups->isDirty()) {
            return true;
        }

        $countries = $specialOffer->getCountries();
        if ($countries instanceof PersistentCollection && $countries->isDirty()) {
            return true;
        }

        return false;
    }

    /**
     * Returns the special offer from the event.
     */
    protected function getSpecialOfferFromEvent(ResourceEventInterface $event): SpecialOfferInterface
    {
        $specialOffer = $event->getResource();

        if (!$specialOffer instanceof SpecialOfferInterface) {
            throw new UnexpectedTypeException($specialOffer, SpecialOfferInterface::class);
        }

        return $specialOffer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SpecialOfferEvents::INSERT => ['onInsert', 0],
            SpecialOfferEvents::UPDATE => ['onUpdate', 0],
            SpecialOfferEvents::DELETE => ['onDelete', 0],
        ];
    }
}
