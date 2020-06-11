<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractMention;

/**
 * Class ProductMention
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductMention extends AbstractMention
{
    /**
     * @var ProductInterface|null
     */
    private $product;


    /**
     * Returns the product.
     *
     * @return ProductInterface|null
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param ProductInterface|null $product
     *
     * @return ProductMention
     */
    public function setProduct(ProductInterface $product = null): ProductMention
    {
        if ($this->product !== $product) {
            if ($previous = $this->product) {
                $this->product = null;
                $previous->removeMention($this);
            }

            if ($this->product = $product) {
                $this->product->addMention($this);
            }
        }

        return $this;
    }
}
