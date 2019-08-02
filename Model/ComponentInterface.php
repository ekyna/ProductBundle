<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Component
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ComponentInterface extends ResourceInterface
{
    /**
     * Returns the parent.
     *
     * @return ProductInterface
     */
    public function getParent(): ?ProductInterface;

    /**
     * Sets the parent.
     *
     * @param ProductInterface $parent
     *
     * @return ComponentInterface
     */
    public function setParent(ProductInterface $parent = null): ComponentInterface;

    /**
     * Returns the child.
     *
     * @return ProductInterface
     */
    public function getChild(): ?ProductInterface;

    /**
     * Sets the child.
     *
     * @param ProductInterface $child
     *
     * @return ComponentInterface
     */
    public function setChild(ProductInterface $child): ComponentInterface;

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity(): ?float;

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return ComponentInterface
     */
    public function setQuantity(float $quantity): ComponentInterface;

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice(): ?float;

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return ComponentInterface
     */
    public function setNetPrice(float $price = null): ComponentInterface;
}