<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\ORM\PersistentCollection;
use Ekyna\Bundle\ProductBundle\Entity\SpecialOffer;
use Ekyna\Bundle\ProductBundle\Event\SpecialOfferEvents;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Bundle\ProductBundle\Service\Generator\PricingNameGenerator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
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
    protected OfferInvalidator           $offerInvalidator;
    protected PriceInvalidator           $priceInvalidator;
    protected PricingNameGenerator       $nameGenerator;
    protected TranslatorInterface        $translator;

    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        OfferInvalidator           $offerInvalidator,
        PriceInvalidator           $priceInvalidator,
        PricingNameGenerator       $nameGenerator,
        TranslatorInterface        $translator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator = $offerInvalidator;
        $this->priceInvalidator = $priceInvalidator;
        $this->nameGenerator = $nameGenerator;
        $this->translator = $translator;
    }

    public function onInsert(ResourceEventInterface $event): SpecialOfferInterface
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->simplify($specialOffer, $event);

        $this->updateName($specialOffer);

        $this->offerInvalidator->invalidateSpecialOffer($specialOffer);

        return $specialOffer;
    }

    public function onUpdate(ResourceEventInterface $event): SpecialOfferInterface
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->simplify($specialOffer, $event);

        $this->updateName($specialOffer);

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

        $this->invalidate($specialOffer, true);

        $this->offerInvalidator->invalidateSpecialOffer($specialOffer);

        // As offers are deleted by DBMS (On delete FK constraint),
        // we need to invalidate product prices here.
        $this->priceInvalidator->invalidateSpecialOffer($specialOffer);

        return $specialOffer;
    }

    /**
     * Invalidates product offers related to this special offer.
     */
    protected function invalidate(SpecialOfferInterface $specialOffer, bool $andPrices = false): void
    {
        $productCs = $this->persistenceHelper->getChangeSet($specialOffer, 'product');

        // Product
        if (!empty($productCs)) {
            /** @var ProductInterface $product */
            foreach ([0, 1] as $key) {
                if (($product = $productCs[$key]) && ($id = $product->getId())) {
                    $this->offerInvalidator->invalidateByProductId($id);
                    if ($andPrices) {
                        $this->priceInvalidator->invalidateByProductId($id);
                    }
                }
            }
        }

        // Products association changes
        foreach ($specialOffer->getInsertedIds(SpecialOffer::REL_PRODUCTS) as $id) {
            $this->offerInvalidator->invalidateByProductId($id);
            if ($andPrices) {
                $this->priceInvalidator->invalidateByProductId($id);
            }
        }
        foreach ($specialOffer->getRemovedIds(SpecialOffer::REL_PRODUCTS) as $id) {
            $this->offerInvalidator->invalidateByProductId($id);
            if ($andPrices) {
                $this->priceInvalidator->invalidateByProductId($id);
            }
        }

        // Brands association changes
        foreach ($specialOffer->getInsertedIds(SpecialOffer::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
            if ($andPrices) {
                $this->priceInvalidator->invalidateByBrandId($id);
            }
        }
        foreach ($specialOffer->getRemovedIds(SpecialOffer::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
            if ($andPrices) {
                $this->priceInvalidator->invalidateByBrandId($id);
            }
        }
    }

    /**
     * Simplifies the special offer by removing products covered by brands.
     */
    protected function simplify(SpecialOfferInterface $specialOffer, ResourceEventInterface $event): void
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

    protected function updateName(SpecialOfferInterface $specialOffer): void
    {
        if (null !== $specialOffer->getProduct()) {
            if (null !== $specialOffer->getName()) {
                $specialOffer->setName(null);
            }

            $this->persistenceHelper->persistAndRecompute($specialOffer, false);

            return;
        }

        $name = $this->nameGenerator->generateSpecialOfferName($specialOffer);

        if ($name === $specialOffer->getName()) {
            return;
        }

        $specialOffer->setName($name);

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
