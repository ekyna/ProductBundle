<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class SimpleHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SimpleHandler extends AbstractHandler
{
    public function __construct(
        private readonly PersistenceHelperInterface   $persistenceHelper,
        private readonly PriceCalculator              $priceCalculator,
        private readonly StockSubjectUpdaterInterface $stockUpdater,
        private readonly ProductRepositoryInterface   $productRepository,
        private readonly OfferInvalidator             $offerInvalidator,
        private readonly PriceInvalidator             $priceInvalidator
    ) {
    }

    // TODO Deal with required option group stocks ?

    public function handleInsert(ResourceEventInterface $event): bool
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $changed = $this->stockUpdater->update($product);

        return $this->updateMinPrice($product) || $changed;
    }

    public function handleUpdate(ResourceEventInterface $event): bool
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $changed = false;
        $childEvents = [];

        $stockProperties = [
            'stockMode',
            'inStock',
            'availableStock',
            'virtualStock',
            'estimatedDateOfArrival',
            'minimumOrderQuantity',
            'releasedAt',
        ];
        if ($this->persistenceHelper->isChanged($product, $stockProperties)) {
            $changed = $this->stockUpdater->update($product);

            $childEvents[] = ProductEvents::CHILD_STOCK_CHANGE;
        } elseif ($this->persistenceHelper->isChanged($product, 'stockState')) {
            $childEvents[] = ProductEvents::CHILD_STOCK_CHANGE;
        }

        if ($this->persistenceHelper->isChanged($product, ['brand', 'pricingGroup'])) {
            $this->offerInvalidator->invalidateByProduct($product);
        } elseif ($this->persistenceHelper->isChanged($product, 'netPrice')) {
            $changed = $this->updateMinPrice($product) || $changed;

            $childEvents[] = ProductEvents::CHILD_PRICE_CHANGE;
        }

        $availabilityProperties = ['visible', 'quoteOnly', 'endOfLife', 'releasedAt'];
        if ($this->persistenceHelper->isChanged($product, $availabilityProperties)) {
            $childEvents[] = ProductEvents::CHILD_AVAILABILITY_CHANGE;

            if ($this->persistenceHelper->isChanged($product, 'visible')) {
                $this->priceInvalidator->invalidateParents($product);
            }
        }

        if (!empty($childEvents)) {
            $this->scheduleChildChangeEvents($product, $childEvents);
        }

        return $changed;
    }

    public function handleStockUnitChange(SubjectStockUnitEvent $event): bool
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $changed = $this->stockUpdater->update($product);

        if ($changed) {
            $this->scheduleChildChangeEvents($product, [ProductEvents::CHILD_STOCK_CHANGE]);
        }

        return $changed;
    }

    public function handleStockUnitRemoval(SubjectStockUnitEvent $event): bool
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if ($this->stockUpdater->update($product)) {
            $this->scheduleChildChangeEvents($product, [ProductEvents::CHILD_STOCK_CHANGE]);

            return true;
        }

        return false;
    }

    public function handleChildStockChange(ResourceEventInterface $event): bool
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if ($this->stockUpdater->update($product)) {
            $this->scheduleChildChangeEvents($product, [ProductEvents::CHILD_STOCK_CHANGE]);

            return true;
        }

        return false;
    }

    public function handleChildPriceChange(ResourceEventInterface $event): bool
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $this->priceInvalidator->invalidateParents($product);

        return $this->updateMinPrice($product);
    }

    public function supports(ProductInterface $product): bool
    {
        return in_array($product->getType(), ProductTypes::getChildTypes());
    }

    /**
     * Updates the product minimum price.
     *
     * @return bool Whether the minimum price has been changed.
     */
    protected function updateMinPrice(ProductInterface $product): bool
    {
        $minPrice = $this->priceCalculator->calculateProductMinPrice($product);
        if (!$product->getMinPrice()->equals($minPrice)) {
            $product->setMinPrice($minPrice);

            $this->offerInvalidator->invalidateByProduct($product);

            return true;
        }

        return true;
    }

    /**
     * Dispatches the child change events.
     */
    protected function scheduleChildChangeEvents(ProductInterface $child, array $events): void
    {
        ProductTypes::assertChildType($child);

        if ($child->getType() === ProductTypes::TYPE_VARIANT) {
            if (null === $variable = $child->getParent()) {
                throw new RuntimeException("Variant's parent must be set.");
            }

            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($variable, $event);
            }
        }

        $parents = $this->productRepository->findParentsByOptionProduct($child, true);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($parent, $event);
            }
        }

        $parents = $this->productRepository->findParentsByBundled($child);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($parent, $event);
            }
        }

        $parents = $this->productRepository->findParentsByComponent($child);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($parent, $event);
            }
        }
    }
}
