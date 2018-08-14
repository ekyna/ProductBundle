<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class VariableUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableUpdater
{
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;


    /**
     * Constructor.
     *
     * @param PriceCalculator $priceCalculator
     */
    public function __construct(PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Indexes the variable's variants position.
     *
     * @param Model\ProductInterface     $variable
     * @param PersistenceHelperInterface $helper
     *
     * @return bool
     */
    public function indexVariantsPositions(Model\ProductInterface $variable, PersistenceHelperInterface $helper = null)
    {
        Model\ProductTypes::assertVariable($variable);

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
            if ($variant->getPosition() != $position || (null === $variant->getPosition() && $position === 0)) {
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

    /**
     * Updates the variable minimum price regarding to its variants.
     *
     * @param Model\ProductInterface $variable
     *
     * @return bool Whether the variable has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateMinPrice(Model\ProductInterface $variable)
    {
        Model\ProductTypes::assertVariable($variable);

        $price = $this->priceCalculator->calculateVariableMinPrice($variable);

        if (is_null($variable->getNetPrice()) || 0 !== bccomp($variable->getNetPrice(), $price, 5)) {
            $variable->setNetPrice($price);

            return true;
        }

        return false;
    }

    /**
     * Updates the variable stock.
     *
     * @param Model\ProductInterface $variable
     *
     * @return bool Whether the variable has been changed or not.
     */
    public function updateStock(Model\ProductInterface $variable)
    {
        Model\ProductTypes::assertVariable($variable);

        // Resolve best variants and copy its data

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $bestVariant */
        $bestVariant = null;

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface[] $variants */
        $variants = $variable->getVariants()->getIterator();
        foreach ($variants as $variant) {
            if (null === $bestVariant) {
                $bestVariant = $variant;
                continue;
            }

            if (Stock\StockSubjectModes::isBetterMode($variant->getStockMode(), $bestVariant->getStockMode())) {
                $bestVariant = $variant;
                continue;
            }

            if (Stock\StockSubjectStates::isBetterState($variant->getStockState(), $bestVariant->getStockState())) {
                $bestVariant = $variant;
                continue;
            }

            if ($bestVariant->getAvailableStock() < $variant->getAvailableStock()) {
                $bestVariant = $variant;
                continue;
            }

            if (0 < $virtualStock = $variant->getVirtualStock()) {
                if (null !== $eda = $variant->getEstimatedDateOfArrival()) {
                    $bestEda = $bestVariant->getEstimatedDateOfArrival();
                    if ((null === $bestVariant) || $bestEda > $eda) {
                        $bestVariant = $variant;
                    }
                } elseif ($bestVariant->getVirtualStock() < $virtualStock) {
                    $bestVariant = $variant;
                    continue;
                }
            }
        }

        $mode = Stock\StockSubjectModes::MODE_AUTO;
        $state = Stock\StockSubjectStates::STATE_OUT_OF_STOCK;
        $inStock = $availableStock = $virtualStock = 0;
        $eda = null;

        if ($bestVariant) {
            $mode = $bestVariant->getStockMode();
            $state = $bestVariant->getStockState();
            $inStock = $bestVariant->getInStock();
            $availableStock = $bestVariant->getAvailableStock();
            $virtualStock = $bestVariant->getVirtualStock();
            $eda = $bestVariant->getEstimatedDateOfArrival();
        }

        $changed = false;

        if ($variable->getInStock() !== $inStock) {
            $variable->setInStock($inStock);
            $changed = true;
        }

        if ($variable->getAvailableStock() !== $availableStock) {
            $variable->setAvailableStock($availableStock);
            $changed = true;
        }

        if ($variable->getVirtualStock() !== $virtualStock) {
            $variable->setVirtualStock($virtualStock);
            $changed = true;
        }

        if ($variable->getEstimatedDateOfArrival() !== $eda) {
            $variable->setEstimatedDateOfArrival($eda);
            $changed = true;
        }

        if ($variable->getStockMode() !== $mode) {
            $variable->setStockMode($mode);
            $changed = true;
        }

        if ($variable->getStockState() !== $state) {
            $variable->setStockState($state);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the given variable availability regarding to its variants.
     *
     * @param Model\ProductInterface $variable
     *
     * @return bool
     */
    public function updateAvailability(Model\ProductInterface $variable)
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
     *
     * @param Model\ProductInterface $variable
     *
     * @return bool
     */
    public function updateVisibility(Model\ProductInterface $variable)
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
}
