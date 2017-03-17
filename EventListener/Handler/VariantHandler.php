<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class VariantHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantHandler extends AbstractVariantHandler
{
    /**
     * @inheritDoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        $changed = false;

        // Generate attributes designation and title if needed
        if ($this->getVariantUpdater()->updateAttributesDesignationAndTitle($variant)) {
            $changed = true;
        }

        // Set tax group regarding to parent/variable if needed
        if ($this->getVariantUpdater()->updateTaxGroup($variant)) {
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        if (null === $variable = $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }

        $changed = false;

        // Generate attributes designation and title if needed
        if ($this->getVariantUpdater()->updateAttributesDesignationAndTitle($variant)) {
            $changed = true;
        }

        // Update parent/variable minimum price if variant price has changed
        if ($this->persistenceHelper->isChanged($variant, 'netPrice')) {
            if ($this->getVariableUpdater()->updateMinPrice($variable)) {
                $this->persistenceHelper->persistAndRecompute($variable, true);
            }
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIANT;
    }
}
