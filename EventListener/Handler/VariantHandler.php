<?php

declare(strict_types=1);

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
    public function handleInsert(ResourceEventInterface $event): bool
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        $updater = $this->getVariantUpdater();

        $changed = false;

        // Generate attributes designation and title if needed
        $changed = $updater->updateAttributesDesignationAndTitle($variant) || $changed;

        // Set tax group regarding to parent/variable if needed
        $changed = $updater->updateTaxGroup($variant) || $changed;

        // Set quantity unit regarding to parent/variable if needed
        $changed = $updater->updateUnit($variant) || $changed;

        // Set brand regarding to parent/variable if needed
        $changed = $updater->updateBrand($variant) || $changed;

        // Updates variant availability
        //$changed = $updater->updateAvailability($variant) || $changed; // TODO Variable may block availability fields update

        if (null !== $variable = $variant->getParent()) {
            $this->getVariableUpdater()->indexVariantsPositions($variable, $this->persistenceHelper);

            $variableChanged = $this->getVariableUpdater()->updateVisibility($variable);
            $variableChanged = $this->getVariableUpdater()->updateMinPrice($variable) || $variableChanged;

            if ($variableChanged) {
                $this->persistenceHelper->persistAndRecompute($variable);
            }
        }

        return $changed;
    }

    public function handleUpdate(ResourceEventInterface $event): bool
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        if (null === $variable = $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }

        $updater = $this->getVariantUpdater();

        // Generate attributes designation and title if needed
        $changed = $updater->updateAttributesDesignationAndTitle($variant);

        // Updates variant availability
        //$changed |= $updater->updateAvailability($variant); // TODO Variable may block availability fields update

        if ($this->persistenceHelper->isChanged($variant, 'position')) {
            $changed = $this
                    ->getVariableUpdater()
                    ->indexVariantsPositions($variable, $this->persistenceHelper) || $changed;
        }

        return $changed;
    }

    public function handleDelete(ResourceEventInterface $event): bool
    {
        $variant = $this->getProductFromEvent($event, ProductTypes::TYPE_VARIANT);

        if (null === $variable = $variant->getParent()) {
            $variable = $this->persistenceHelper->getChangeSet($variant, 'parent')[0];
        }
        if (null !== $variable) {
            $variableUpdater = $this->getVariableUpdater();

            if (!$this->persistenceHelper->isScheduledForRemove($variable)) {
                $variableUpdater->indexVariantsPositions($variable, $this->persistenceHelper);

                $variant->setVisible(false);
                $changed = $variableUpdater->updateAvailability($variable);
                $changed = $variableUpdater->updateVisibility($variable) || $changed;
                $changed = $variableUpdater->updateMinPrice($variable) || $changed;

                if ($changed) {
                    $this->persistenceHelper->persistAndRecompute($variable);
                }
            }
        }

        return false;
    }

    public function supports(ProductInterface $product): bool
    {
        return $product->getType() === ProductTypes::TYPE_VARIANT;
    }
}
