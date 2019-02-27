<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Entity\Pricing;
use Ekyna\Bundle\ProductBundle\Event\PricingEvents;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PricingListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingListener implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var OfferInvalidator
     */
    protected $offerInvalidator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param OfferInvalidator           $offerInvalidator
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper, OfferInvalidator $offerInvalidator)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator = $offerInvalidator;
    }

    /**
     * Pre insert event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingInterface
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->buildName($pricing);

        $this->offerInvalidator->invalidatePricing($pricing);

        return $pricing;
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingInterface
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->buildName($pricing);

        // Brands association changes
        foreach ($pricing->getInsertedIds(Pricing::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
        }
        foreach ($pricing->getRemovedIds(Pricing::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
        }

        return $pricing;
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingInterface
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->buildName($pricing);

        $this->offerInvalidator->invalidatePricing($pricing);

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

        if (null !== $product = $pricing->getProduct()) {
            if (32 > strlen($designation = $product->getDesignation())) {
                $parts[] = $designation;
            } else {
                $parts[] = substr($designation, 0, 32) . '...';
            }
        } else {
            $parts[] = implode('/', array_map(function (BrandInterface $brand) {
                return $brand->getName();
            }, $pricing->getBrands()->toArray()));
        }

        if (!empty($groups = $pricing->getGroups()->toArray())) {
            $parts[] = implode('/', array_map(function (CustomerGroupInterface $group) {
                return $group->getName();
            }, $groups));
        }

        if (!empty($countries = $pricing->getCountries()->toArray())) {
            $parts[] = implode('/', array_map(function (CountryInterface $country) {
                return $country->getName();
            }, $countries));
        }

        $pricing->setName(implode(' - ', $parts));

        $this->persistenceHelper->persistAndRecompute($pricing, false);
    }

    /**
     * Returns the pricing from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingInterface
     */
    protected function getPricingFromEvent(ResourceEventInterface $event)
    {
        $pricing = $event->getResource();

        if (!$pricing instanceof PricingInterface) {
            throw new InvalidArgumentException("Expected instance of " . PricingInterface::class);
        }

        return $pricing;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PricingEvents::INSERT => ['onInsert', 0],
            PricingEvents::UPDATE => ['onUpdate', 0],
            PricingEvents::DELETE => ['onDelete', 0],
        ];
    }
}
