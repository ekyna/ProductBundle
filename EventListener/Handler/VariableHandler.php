<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class VariableHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableHandler extends AbstractVariantHandler
{
    /**
     * @var PriceInvalidator
     */
    private $priceInvalidator;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockUpdater;


    /**
     * Sets the price invalidator.
     *
     * @param PriceInvalidator $invalidator
     */
    public function setPriceInvalidator($invalidator): void
    {
        $this->priceInvalidator = $invalidator;
    }

    /**
     * Sets the stock updater.
     *
     * @param StockSubjectUpdaterInterface $updater
     */
    public function setStockUpdater(StockSubjectUpdaterInterface $updater): void
    {
        $this->stockUpdater = $updater;
    }

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        $updater = $this->getVariableUpdater();

        $changed = $updater->updateAvailability($variable);

        $changed |= $updater->updateNetPrice($variable);

        $changed |= $updater->updateMinPrice($variable);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        $variantIds = [];
        $variants = [];

        $addVariant = function(ProductInterface $variant) use (&$variants, &$variantIds) {
            if (!in_array($variant->getId(), $variantIds)) {
                $variantIds[] = $variant->getId();
                $variants[] = $variant;
            }
        };

        $updater = $this->getVariantUpdater();

        $changeSet = $this->persistenceHelper->getChangeSet($variable);

        if (isset($changeSet['taxGroup'])) {
            foreach ($variable->getVariants() as $variant) {
                if ($updater->updateTaxGroup($variant)) {
                    $addVariant($variant);
                }
            }
        }
        if (isset($changeSet['unit'])) {
            foreach ($variable->getVariants() as $variant) {
                if ($updater->updateUnit($variant)) {
                    $addVariant($variant);
                }
            }
        }
        if (isset($changeSet['brand'])) {
            foreach ($variable->getVariants() as $variant) {
                if ($updater->updateBrand($variant)) {
                    $addVariant($variant);
                }
            }
        }
        if (isset($changeSet['visible']) && !$variable->isVisible()) {
            foreach ($variable->getVariants() as $variant) {
                if ($variant->isVisible()) {
                    $variant->setVisible(false);
                    $addVariant($variant);
                }
            }
        }
        if (isset($changeSet['quoteOnly']) && $variable->isQuoteOnly()) {
            foreach ($variable->getVariants() as $variant) {
                if (!$variant->isQuoteOnly()) {
                    $variant->setQuoteOnly(true);
                    $addVariant($variant);
                }
            }
        }
        if (isset($changeSet['endOfLife']) && $variable->isEndOfLife()) {
            foreach ($variable->getVariants() as $variant) {
                if (!$variant->isEndOfLife()) {
                    $variant->setEndOfLife(true);
                    $addVariant($variant);
                }
            }
        }

        foreach ($variants as $variant) {
            $this->persistenceHelper->persistAndRecompute($variant);
        }

        $changed = false;
        $childEvents = [];

        if ($this->getVariableUpdater()->updateAvailability($variable)) {
            $changed = true;
            $childEvents[] = ProductEvents::CHILD_AVAILABILITY_CHANGE;
        }

        $stockProperties = [
            'inStock', 'availableStock', 'virtualStock', 'estimatedDateOfArrival', 'stockMode', 'stockState'
        ];
        if ($this->persistenceHelper->isChanged($variable, $stockProperties)) {
            $childEvents[] = ProductEvents::CHILD_STOCK_CHANGE;
        }

        if (!empty($childEvents)) {
            $this->scheduleChildChangeEvents($variable, $childEvents);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildPriceChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        $this->priceInvalidator->invalidateByProduct($variable);

        $updater = $this->getVariableUpdater();

        $changed = $updater->updateNetPrice($variable);
        $changed |= $updater->updateMinPrice($variable);

        if ($changed) {
            $this->scheduleChildChangeEvents($variable, [ProductEvents::CHILD_PRICE_CHANGE]);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildAvailabilityChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        $updater = $this->getVariableUpdater();

        $changed = $updater->updateAvailability($variable);
        $changed |= $updater->updateNetPrice($variable);
        $changed |= $updater->updateMinPrice($variable);
        $changed |= $updater->updateVisibility($variable);

        if ($changed) {
            $this->scheduleChildChangeEvents($variable, [ProductEvents::CHILD_AVAILABILITY_CHANGE]);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->stockUpdater->update($variable)) {
            $this->scheduleChildChangeEvents($variable, [ProductEvents::CHILD_STOCK_CHANGE]);

            return true;
        }

        return false;
    }

    /**
     * Dispatches the child change events.
     *
     * @param ProductInterface $variable
     * @param array            $events
     */
    protected function scheduleChildChangeEvents(ProductInterface $variable, array $events)
    {
        ProductTypes::assertVariable($variable);

        $parents = $this->productRepository->findParentsByBundled($variable);

        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $parent);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIABLE;
    }
}
