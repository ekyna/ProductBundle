<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\ORM\PersistentCollection;
use Ekyna\Bundle\ProductBundle\Entity\SpecialOffer;
use Ekyna\Bundle\ProductBundle\Event\SpecialOfferEvents;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SpecialOfferEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferEventSubscriber implements EventSubscriberInterface
{
    private const FIELDS = ['percent', 'minQuantity', 'startsAt', 'endsAt', 'enabled'];

    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var OfferInvalidator
     */
    private $offerInvalidator;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param OfferInvalidator           $offerInvalidator
     * @param TranslatorInterface        $translator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        OfferInvalidator $offerInvalidator,
        TranslatorInterface $translator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator = $offerInvalidator;
        $this->translator = $translator;
    }

    /**
     * Pre insert event handler.
     *
     * @param ResourceEvent $event
     */
    public function onPreInsert(ResourceEvent $event)
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->simplify($specialOffer, $event);

        $this->buildName($specialOffer);
        //$this->buildDesignation($specialOffer);

        $this->offerInvalidator->invalidateSpecialOffer($specialOffer);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEvent $event
     */
    public function onPreUpdate(ResourceEvent $event)
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->simplify($specialOffer, $event);

        $this->buildName($specialOffer);
        //$this->buildDesignation($specialOffer);

        // Products association changes
        foreach ($specialOffer->getInsertedIds(SpecialOffer::REL_PRODUCTS) as $id) {
            $this->offerInvalidator->invalidateByProductId($id);
        }
        foreach ($specialOffer->getRemovedIds(SpecialOffer::REL_PRODUCTS) as $id) {
            $this->offerInvalidator->invalidateByProductId($id);
        }

        // Brands association changes
        foreach ($specialOffer->getInsertedIds(SpecialOffer::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
        }
        foreach ($specialOffer->getRemovedIds(SpecialOffer::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
        }
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEvent $event
     */
    public function onPreDelete(ResourceEvent $event)
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        $this->offerInvalidator->invalidateSpecialOffer($specialOffer);
    }

    /**
     * Simplifies the special offer by removing products covered by brands.
     *
     * @param SpecialOfferInterface $specialOffer
     * @param ResourceEvent         $event
     */
    protected function simplify(SpecialOfferInterface $specialOffer, ResourceEvent $event)
    {
        $brandIds = [];

        foreach ($specialOffer->getBrands() as $brand) {
            $brandIds[] = $brand->getId();
        }

        $messages = [];

        foreach ($specialOffer->getProducts() as $product) {
            $brand = $product->getBrand();
            if (in_array($brand->getId(), $brandIds)) {
                $specialOffer->removeProduct($product);

                $messages[] = $this->translator->trans('ekyna_product.special_offer.message.product_removed', [
                    '{product}' => $product->getFullDesignation(),
                    '{brand}'   => $brand->getName(),
                ]);
            }
        }

        if (!empty($messages)) {
            $event->addMessage(new ResourceMessage(implode('<br>', $messages)));
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEvent $event
     */
    public function onUpdate(ResourceEvent $event)
    {
        $specialOffer = $this->getSpecialOfferFromEvent($event);

        // Special offer change(s)
        if ($this->specialOfferHasChanged($specialOffer)) {
            $this->offerInvalidator->invalidateSpecialOffer($specialOffer);
        }
    }

    /**
     * Returns whether the given special offer has changed.
     *
     * @param SpecialOfferInterface $specialOffer
     *
     * @return bool
     */
    private function specialOfferHasChanged(SpecialOfferInterface $specialOffer)
    {
        if ($this->persistenceHelper->isChanged($specialOffer, static::FIELDS)) {
            return true;
        }

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
     *
     * @param ResourceEvent $event
     *
     * @return SpecialOfferInterface
     */
    private function getSpecialOfferFromEvent(ResourceEvent $event)
    {
        $specialOffer = $event->getResource();

        if (!$specialOffer instanceof SpecialOfferInterface) {
            throw new InvalidArgumentException("Expected instance of " . SpecialOfferInterface::class);
        }

        return $specialOffer;
    }

    /**
     * Builds the special offer name.
     *
     * @param SpecialOfferInterface $specialOffer
     */
    public function buildName(SpecialOfferInterface $specialOffer)
    {
        if (0 < strlen($specialOffer->getName())) {
            return;
        }

        $parts = [$specialOffer->getPercent() . '%'];

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

        $brands = $specialOffer->getBrands()->toArray();

        if (empty($brands)) {
            $parts[] = $specialOffer->getProducts()->count() . ' product(s)';
        } else {
            $parts[] = implode('/', array_map(function (BrandInterface $brand) {
                return $brand->getName();
            }, $specialOffer->getBrands()->toArray()));
        }

        $specialOffer->setName(implode(' - ', $parts));
    }

    /**
     * Builds the special offer designation.
     *
     * @param SpecialOfferInterface $specialOffer
     */
//    public function buildDesignation(SpecialOfferInterface $specialOffer)
//    {
//        if (0 < strlen($specialOffer->getDesignation())) {
//            return;
//        }
//
//        $groups = implode('/', array_map(function(CustomerGroupInterface $group) {
//            return $group->getName();
//        }, $specialOffer->getGroups()->toArray()));
//
//        $specialOffer->setDesignation('Remise ' . $groups);
//    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SpecialOfferEvents::PRE_CREATE => ['onPreInsert', 0],
            SpecialOfferEvents::PRE_UPDATE => ['onPreUpdate', 0],
            SpecialOfferEvents::PRE_DELETE => ['onPreDelete', 0],
            SpecialOfferEvents::UPDATE     => ['onUpdate', 0],
        ];
    }
}
