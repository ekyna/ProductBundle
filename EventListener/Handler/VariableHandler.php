<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

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

        return $this->getVariableUpdater()->updateAvailability($variable);
    }

    /**
     * @inheritdoc
     */
    public function handleChildPriceChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->getVariableUpdater()->updateMinPrice($variable);
    }

    /**
     * @inheritDoc
     */
    public function handleChildAvailabilityChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->getVariableUpdater()->updateAvailability($variable);
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->getVariableUpdater()->updateStock($variable);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIABLE;
    }
}
