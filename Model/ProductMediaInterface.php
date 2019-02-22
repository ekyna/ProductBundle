<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\GalleryMediaInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ProductMediaInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductMediaInterface extends GalleryMediaInterface, ResourceInterface
{
    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return ProductMediaInterface
     */
    public function setProduct(ProductInterface $product = null);

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct();
}
