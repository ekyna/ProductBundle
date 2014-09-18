<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Sale\Product\OptionInterface;
use Ekyna\Component\Sale\Product\ProductInterface;

/**
 * AbstractOption.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractOption implements OptionInterface
{
    use \Ekyna\Component\Sale\PriceableTrait;
    use \Ekyna\Component\Sale\ReferenceableTrait;
    use \Ekyna\Component\Sale\WeighableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \Ekyna\Component\Sale\Product\ProductInterface
     */
    protected $product;

    /**
     * @var string
     */
    protected $group;


    /**
     * Returns the string represenation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDesignation();
    }

    /**
     * Returns the identifier.
     * 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the product.
     *
     * @param \Ekyna\Component\Sale\Product\ProductInterface $product
     * 
     * @return Option
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets the group.
     *
     * @param string $group
     * 
     * @return Option
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return $this->group;
    }
}
