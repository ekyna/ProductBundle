<?php

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Class ProductEntry
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEntry
{
    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var int
     */
    private $position;


    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return ProductEntry
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the position.
     *
     * @param int $position
     *
     * @return ProductEntry
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
