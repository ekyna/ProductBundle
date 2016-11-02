<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\GalleryMediaInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ProductImageInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductImageInterface extends GalleryMediaInterface, ResourceInterface
{
    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return ProductImageInterface
     */
    public function setProduct(ProductInterface $product = null);

    /**
     * Returns the product.
     *
     * @return ProductImageInterface
     */
    public function getProduct();
}
