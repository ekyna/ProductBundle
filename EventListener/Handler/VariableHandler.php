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

        $changed = $this->ensureDisabledStockMode($variable);

        $changed |= $this->checkVisibility($variable);

        $changed |= $this->getVariableUpdater()->updateMinPrice($variable);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        if ($this->persistenceHelper->isChanged($variable, 'taxGroup')) {
            foreach ($variable->getVariants() as $variant) {
                if ($this->getVariantUpdater()->updateTaxGroup($variant)) {
                    $this->persistenceHelper->persistAndRecompute($variant);
                }
            }
        }
        if ($this->persistenceHelper->isChanged($variable, 'brand')) {
            foreach ($variable->getVariants() as $variant) {
                if ($this->getVariantUpdater()->updateBrand($variant)) {
                    $this->persistenceHelper->persistAndRecompute($variant);
                }
            }
        }

        $changed = $this->ensureDisabledStockMode($variable);

        $changed |= $this->checkVisibility($variable);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildDataChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->getVariableUpdater()->updateMinPrice($variable);
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $variable = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIABLE);

        return $this->getVariableUpdater()->updateStockState($variable);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIABLE;
    }
}
