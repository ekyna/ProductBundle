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
        $changed |= $this->getVariantUpdater()->updateAttributesDesignationAndTitle($variant);

        // Set tax group regarding to parent/variable if needed
        $changed |= $this->getVariantUpdater()->updateTaxGroup($variant);

        // Set brand regarding to parent/variable if needed
        $changed |= $this->getVariantUpdater()->updateBrand($variant);

        if (null !== $variable = $variant->getParent()) {
            if (null === $variant->getPosition()) {
                $variant->setPosition(9999);
            }
            $this->getVariableUpdater()->indexVariantsPositions($variable, $this->persistenceHelper);
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
        $changed |= $this->getVariantUpdater()->updateAttributesDesignationAndTitle($variant);

        if (null !== $variable = $variant->getParent()) {
            if ($this->persistenceHelper->isChanged($variant, 'position')) {
                $this->getVariableUpdater()->indexVariantsPositions($variable, $this->persistenceHelper);
            }
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
            if (!$this->persistenceHelper->isScheduledForRemove($variable)) {
                $this->getVariableUpdater()->indexVariantsPositions($variable, $this->persistenceHelper);

                if ($this->checkVisibility($variable)) {
                    $this->persistenceHelper->persistAndRecompute($variable);
                }
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
