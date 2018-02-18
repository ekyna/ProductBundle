<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class VariableHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableHandler extends AbstractVariantHandler
{
    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        $updater = $this->getVariableUpdater();

        $changed = $updater->updateAvailability($variable);

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

        $variantUpdater = $this->getVariantUpdater();

        $changeSet = $this->persistenceHelper->getChangeSet($variable);

        if (isset($changeSet['taxGroup'])) {
            foreach ($variable->getVariants() as $variant) {
                if ($variantUpdater->updateTaxGroup($variant)) {
                    $addVariant($variant);
                }
            }
        }
        if (isset($changeSet['brand'])) {
            foreach ($variable->getVariants() as $variant) {
                if ($variantUpdater->updateBrand($variant)) {
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

        if ($this->getVariableUpdater()->updateMinPrice($variable)) {
            $this->scheduleChildChangeEvents($variable, [ProductEvents::CHILD_PRICE_CHANGE]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildAvailabilityChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->getVariableUpdater()->updateAvailability($variable)) {
            $this->scheduleChildChangeEvents($variable, [ProductEvents::CHILD_AVAILABILITY_CHANGE]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->getVariableUpdater()->updateStock($variable)) {
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
