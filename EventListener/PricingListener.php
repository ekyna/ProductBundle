<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Entity\Pricing;
use Ekyna\Bundle\ProductBundle\Event\PricingEvents;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
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
     * @var PriceInvalidator
     */
    protected $priceInvalidator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param OfferInvalidator           $offerInvalidator
     * @param PriceInvalidator           $priceInvalidator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        OfferInvalidator $offerInvalidator,
        PriceInvalidator $priceInvalidator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator  = $offerInvalidator;
        $this->priceInvalidator  = $priceInvalidator;
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

        $this->invalidate($pricing);

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

        $this->invalidate($pricing, true);

        $this->offerInvalidator->invalidatePricing($pricing);

        // As offers are deleted by DBMS (On delete FK constraint),
        // we need to invalidate product prices here.
        $this->priceInvalidator->invalidatePricing($pricing);

        return $pricing;
    }

    /**
     * Invalidates product offers related to this pricing.
     *
     * @param PricingInterface $pricing
     * @param bool             $andPrices
     */
    protected function invalidate(PricingInterface $pricing, bool $andPrices = false)
    {
        $productCs = $this->persistenceHelper->getChangeSet($pricing, 'product');

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

        // Brands association changes
        foreach ($pricing->getInsertedIds(Pricing::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
        }
        foreach ($pricing->getRemovedIds(Pricing::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
        }
    }

    /**
     * Builds the pricing name.
     *
     * @param PricingInterface $pricing
     */
    protected function buildName(PricingInterface $pricing)
    {
        if (!empty($pricing->getName())) {
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
