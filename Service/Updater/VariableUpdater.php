<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
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

                if ($helper) {
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

        $variants = $variable->getVariants()->getIterator();
        if (0 == count($variants)) {
            if (0 != $variable->getNetPrice()) {
                $variable->setNetPrice(0);

                return true;
            }

            return false;
        }

        $minPrice = null;
        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $variant */
        foreach ($variants as $variant) {
            if (null === $minPrice || $minPrice > $variant->getNetPrice()) {
                $minPrice = $variant->getNetPrice();
            }
        }

        if (null !== $minPrice && 0 !== bccomp($variable->getNetPrice(), $minPrice, 5)) {
            $variable->setNetPrice($minPrice);

            return true;
        }

        return false;
    }

    /**
     * Updates the variable stock state.
     *
     * @param Model\ProductInterface $variable
     *
     * @return bool Whether the variable has been changed or not.
     */
    public function updateStockState(Model\ProductInterface $variable)
    {
        Model\ProductTypes::assertVariable($variable);

        $state = Stock\StockSubjectStates::STATE_OUT_OF_STOCK;
        $inStock = $availableStock = $virtualStock = null;
        $variants = $variable->getVariants()->getIterator();

        // TODO This is wrong
        // -> Resolve best variants and copy its data

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $variant */
        foreach ($variants as $variant) {
            if ($variant->getStockMode() == Stock\StockSubjectModes::MODE_DISABLED) {
                continue;
            } elseif ($variant->getStockMode() == Stock\StockSubjectModes::MODE_JUST_IN_TIME) {
                $state = Stock\StockSubjectStates::STATE_IN_STOCK;
                $inStock = $virtualStock = 0;
                break;
            }

            // TODO NO ! Resolve state regarding to stocks
            if (Stock\StockSubjectStates::isBetterState($variant->getStockState(), $state)) {
                $state = $variant->getStockState();
            }

            $variantInStock = $variant->getInStock();
            if (null === $inStock || (0 < $variantInStock && $inStock > $variantInStock)) {
                $inStock = $variantInStock;
            }

            $variantAvailableStock = $variant->getAvailableStock();
            if (null === $availableStock || (0 < $variantAvailableStock && $availableStock > $variantAvailableStock)) {
                $inStock = $variantAvailableStock;
            }

            $variantVirtualStock = $variant->getVirtualStock();
            if (null === $virtualStock || (0 < $variantVirtualStock && $virtualStock > $variantVirtualStock)) {
                $virtualStock = $variantVirtualStock;
            }
        }

        $changed = false;

        if ($variable->getInStock() !== $inStock) {
            $variable->setInStock($inStock);
            $changed = true;
        }

        if ($variable->getVirtualStock() !== $virtualStock) {
            $variable->setVirtualStock($virtualStock);
            $changed = true;
        }

        if ($variable->getStockState() !== $state) {
            $variable->setStockState($state);
            $changed = true;
        }

        return $changed;
    }
}
