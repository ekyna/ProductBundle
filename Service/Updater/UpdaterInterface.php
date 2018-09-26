<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Interface UpdaterInterface
 * @package Ekyna\Bundle\ProductBundle\Service\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface UpdaterInterface
{
    /**
     * Updates the product min price.
     *
     * @param ProductInterface $product
     *
     * @return bool Whether the product has been changed or not.
     */
    public function updateMinPrice(ProductInterface $product);

    /**
     * Updates the product stock.
     *
     * @param ProductInterface $product
     *
     * @return bool Whether the product has been changed or not.
     */
    public function updateStock(ProductInterface $product);

    /**
     * Updates the product availability.
     *
     * @param ProductInterface $product
     *
     * @return bool Whether the product has been changed or not.
     */
    public function updateAvailability(ProductInterface $product);
}