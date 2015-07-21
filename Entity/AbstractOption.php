<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Sale;
use Ekyna\Component\Sale\Product\OptionInterface;
use Ekyna\Component\Sale\Product\ProductInterface;

/**
 * Class AbstractOption
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractOption implements OptionInterface
{
    use Sale\PriceableTrait,
        Sale\ReferenceableTrait,
        Sale\WeightableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var string
     */
    protected $group;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDesignation();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
