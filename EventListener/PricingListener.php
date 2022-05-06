<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Entity\Pricing;
use Ekyna\Bundle\ProductBundle\Event\PricingEvents;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Generator\PricingNameGenerator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PricingListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingListener implements EventSubscriberInterface
{
    protected PersistenceHelperInterface $persistenceHelper;
    protected OfferInvalidator           $offerInvalidator;
    protected PriceInvalidator           $priceInvalidator;
    protected PricingNameGenerator       $nameGenerator;

    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        OfferInvalidator           $offerInvalidator,
        PriceInvalidator           $priceInvalidator,
        PricingNameGenerator       $nameGenerator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator = $offerInvalidator;
        $this->priceInvalidator = $priceInvalidator;
        $this->nameGenerator = $nameGenerator;
    }

    public function onInsert(ResourceEventInterface $event): PricingInterface
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->updateName($pricing);

        $this->offerInvalidator->invalidatePricing($pricing);

        return $pricing;
    }

    public function onUpdate(ResourceEventInterface $event): PricingInterface
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->updateName($pricing);

        $this->invalidate($pricing);

        return $pricing;
    }

    public function onDelete(ResourceEventInterface $event): PricingInterface
    {
        $pricing = $this->getPricingFromEvent($event);

        $this->invalidate($pricing, true);

        $this->offerInvalidator->invalidatePricing($pricing);

        // As offers are deleted by DBMS (On delete FK constraint),
        // we need to invalidate product prices here.
        $this->priceInvalidator->invalidatePricing($pricing);

        return $pricing;
    }

    /**
     * Invalidates product offers related to this pricing.
     */
    protected function invalidate(PricingInterface $pricing, bool $andPrices = false): void
    {
        $productCs = $this->persistenceHelper->getChangeSet($pricing, 'product');

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

        // Brands association changes
        foreach ($pricing->getInsertedIds(Pricing::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
            if ($andPrices) {
                $this->priceInvalidator->invalidateByBrandId($id);
            }
        }
        foreach ($pricing->getRemovedIds(Pricing::REL_BRANDS) as $id) {
            $this->offerInvalidator->invalidateByBrandId($id);
            if ($andPrices) {
                $this->priceInvalidator->invalidateByBrandId($id);
            }
        }
    }

    protected function updateName(PricingInterface $pricing): void
    {
        if (null !== $pricing->getProduct()) {
            if (null !== $pricing->getName()) {
                $pricing->setName(null);
            }

            $this->persistenceHelper->persistAndRecompute($pricing, false);

            return;
        }

        $name = $this->nameGenerator->generatePricingName($pricing);

        if ($name === $pricing->getName()) {
            return;
        }

        $pricing->setName($name);

        $this->persistenceHelper->persistAndRecompute($pricing, false);
    }

    /**
     * Returns the pricing from the event.
     */
    protected function getPricingFromEvent(ResourceEventInterface $event): PricingInterface
    {
        $pricing = $event->getResource();

        if (!$pricing instanceof PricingInterface) {
            throw new UnexpectedTypeException($pricing, PricingInterface::class);
        }

        return $pricing;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PricingEvents::INSERT => ['onInsert', 0],
            PricingEvents::UPDATE => ['onUpdate', 0],
            PricingEvents::DELETE => ['onDelete', 0],
        ];
    }
}
