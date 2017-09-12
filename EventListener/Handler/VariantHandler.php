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

        // Set brand regarding to parent/variable if needed
        if ($this->getVariantUpdater()->updateBrand($variant)) {
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

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function handleDelete(ResourceEventInterface $event)
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        if (null !== $variable = $variant->getParent()) {
            if (!$this->persistenceHelper->isScheduledForRemove($variable) && $this->checkVisibility($variable)) {
                $this->persistenceHelper->persistAndRecompute($variable);
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_VARIANT;
    }
}
