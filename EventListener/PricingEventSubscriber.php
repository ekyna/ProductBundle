<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Entity\Pricing;
use Ekyna\Bundle\ProductBundle\Event\PricingEvents;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PricingEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var OfferInvalidator
     */
    private $offerInvalidator;


    /**
     * Constructor.
     *
     * @param OfferInvalidator $offerInvalidator
     */
    public function __construct(OfferInvalidator $offerInvalidator)
    {
        $this->offerInvalidator = $offerInvalidator;
    }

    /**
     * Pre insert event handler.
     *
     * @param ResourceEvent $event
     */
    public function onPreInsert(ResourceEvent $event)
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->buildName($pricing);
        $this->buildDesignation($pricing);

        $this->invalidateOffers($pricing);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEvent $event
     */
    public function onPreUpdate(ResourceEvent $event)
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->buildName($pricing);
        $this->buildDesignation($pricing);

        // Brands association changes
        foreach ($pricing->getInsertedIds(Pricing::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
        }
        foreach ($pricing->getRemovedIds(Pricing::REL_BRANDS) as $id) {
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
        $pricing = $this->getPricingFromEvent($event);

        $this->buildName($pricing);
        $this->buildDesignation($pricing);

        $this->invalidateOffers($pricing);
    }

    /**
     * Invalidates offers for the given pricing.
     *
     * @param PricingInterface $pricing
     */
    private function invalidateOffers(PricingInterface $pricing)
    {
        foreach ($pricing->getBrands() as $brand) {
            $this->offerInvalidator->invalidateByBrandId($brand->getId());
        }
    }

    /**
     * Returns the pricing from the event.
     *
     * @param ResourceEvent $event
     *
     * @return PricingInterface
     */
    protected function getPricingFromEvent(ResourceEvent $event)
    {
        $pricing = $event->getResource();

        if (!$pricing instanceof PricingInterface) {
            throw new InvalidArgumentException("Expected instance of " . PricingInterface::class);
        }

        return $pricing;
    }

    /**
     * Builds the pricing name.
     *
     * @param PricingInterface $pricing
     */
    protected function buildName(PricingInterface $pricing)
    {
        if (0 < strlen($pricing->getName())) {
            return;
        }

        $parts = [];

        $parts[] = implode('/', array_map(function (CustomerGroupInterface $group) {
            return $group->getName();
        }, $pricing->getGroups()->toArray()));

        $parts[] = implode('/', array_map(function (CountryInterface $country) {
            return $country->getName();
        }, $pricing->getCountries()->toArray()));

        $parts[] = implode('/', array_map(function (BrandInterface $brand) {
            return $brand->getName();
        }, $pricing->getBrands()->toArray()));

        $pricing->setName(implode(' - ', $parts));
    }

    /**
     * Builds the pricing designation.
     *
     * @param PricingInterface $pricing
     */
    protected function buildDesignation(PricingInterface $pricing)
    {
        if (0 < strlen($pricing->getDesignation())) {
            return;
        }

        $groups = implode('/', array_map(function (CustomerGroupInterface $group) {
            return $group->getName();
        }, $pricing->getGroups()->toArray()));

        $pricing->setDesignation('Remise ' . $groups); // TODO
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PricingEvents::PRE_CREATE => ['onPreInsert', 0],
            PricingEvents::PRE_UPDATE => ['onPreUpdate', 0],
            PricingEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
