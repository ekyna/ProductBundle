<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model as Stock;

/**
 * Class VariableUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableUpdater
{
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

        if (!$variable->getStockMode() === Stock\StockSubjectModes::MODE_ENABLED) {
            return false;
        }

        $state = Stock\StockSubjectStates::STATE_OUT_OF_STOCK;
        $variants = $variable->getVariants()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $variant */
        foreach ($variants as $variant) {
            if (Stock\StockSubjectStates::isBetterState($variant->getStockState(), $state)) {
                $state = $variant->getStockState();
            }
        }

        if ($variable->getStockState() !== $state) {
            $variable->setStockState($state);

            return true;
        }

        return false;
    }
}
