<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use ArrayIterator;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class VariableUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableUpdater extends AbstractUpdater
{
    /**
     * Updates the variable product min price.
     */
    public function updateNetPrice(Model\ProductInterface $variable): bool
    {
        Model\ProductTypes::assertVariable($variable);

        $netPrice = $this->priceCalculator->calculateComponentsPrice($variable);

        if (!$variable->getNetPrice()->equals($netPrice)) {
            $variable->setNetPrice($netPrice);

            return true;
        }

        return false;
    }

    /**
     * Updates the variable product min price.
     */
    public function updateMinPrice(Model\ProductInterface $variable): bool
    {
        Model\ProductTypes::assertVariable($variable);

        $minPrice = $this->priceCalculator->calculateVariableMinPrice($variable);

        if (!$variable->getMinPrice()->equals($minPrice)) {
            $variable->setMinPrice($minPrice);

            return true;
        }

        return false;
    }

    /**
     * Updates the variable availability.
     */
    public function updateAvailability(Model\ProductInterface $variable): bool
    {
        Model\ProductTypes::assertVariable($variable);

        $changed = false;

        if (0 === $variable->getVariants()->count()) {
            $quoteOnly = $endOfLife = false;

            if ($variable->isVisible()) {
                $variable->setVisible(false);
                $changed = true;
            }
        } else {
            $quoteOnly = $endOfLife = true;

            foreach ($variable->getVariants() as $variant) {
                if (!$variant->isQuoteOnly()) {
                    $quoteOnly = false;
                }
                if (!$variant->isEndOfLife()) {
                    $endOfLife = false;
                }
                if (!$quoteOnly && !$endOfLife) {
                    break;
                }
            }
        }

        if ($quoteOnly != $variable->isQuoteOnly()) {
            $variable->setQuoteOnly($quoteOnly);
            $changed = true;
        }
        if ($endOfLife != $variable->isEndOfLife()) {
            $variable->setEndOfLife($endOfLife);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the variable visibility.
     */
    public function updateVisibility(Model\ProductInterface $variable): bool
    {
        Model\ProductTypes::assertVariable($variable);

        $changed = false;

        $hasVisibleVariant = false;

        foreach ($variable->getVariants() as $variant) {
            if ($variant->isVisible()) {
                $hasVisibleVariant = true;
                break;
            }
        }

        if ($hasVisibleVariant && !$variable->isVisible()) {
            $variable->setVisible(true);
            $changed = true;
        } elseif (!$hasVisibleVariant && $variable->isVisible()) {
            $variable->setVisible(false);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Indexes the variable's variants position.
     */
    public function indexVariantsPositions(
        Model\ProductInterface     $variable,
        PersistenceHelperInterface $helper = null
    ): bool {
        Model\ProductTypes::assertVariable($variable);

        /** @var ArrayIterator<Model\ProductInterface> $variants */
        $variants = $variable->getVariants()->getIterator();

        $changed = false;

        // Sort with current position
        $variants->uasort(function (Model\ProductInterface $vA, Model\ProductInterface $vB) {
            if ($vA->getPosition() === $vB->getPosition()) {
                return 0;
            }

            return $vA->getPosition() > $vB->getPosition() ? 1 : -1;
        });

        // Update positions if needed
        $position = 0;
        /** @var Model\ProductInterface $variant */
        foreach ($variants as $variant) {
            if ($variant->getPosition() !== $position) {
                $variant->setPosition($position);

                if ($helper && !$helper->isScheduledForRemove($variant)) {
                    $helper->persistAndRecompute($variant);
                }

                $changed = true;
            }

            $position++;
        }

        return $changed;
    }
}
