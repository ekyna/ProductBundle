<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ComponentInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

class Component implements ComponentInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var ProductInterface
     */
    private $parent;

    /**
     * @var ProductInterface
     */
    private $child;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var float
     */
    private $netPrice;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->quantity = 1;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->child;
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?ProductInterface
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function setParent(ProductInterface $parent = null): ComponentInterface
    {
        if ($this->parent !== $parent) {
            if ($previous = $this->parent) {
                $this->parent = null;
                $previous->removeComponent($this);
            }

            if ($this->parent = $parent) {
                $this->parent->addComponent($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChild(): ?ProductInterface
    {
        return $this->child;
    }

    /**
     * @inheritDoc
     */
    public function setChild(ProductInterface $child): ComponentInterface
    {
        $this->child = $child;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    /**
     * @inheritDoc
     */
    public function setQuantity(float $quantity = null): ComponentInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNetPrice(): ?float
    {
        return $this->netPrice;
    }

    /**
     * @inheritDoc
     */
    public function setNetPrice(float $price = null): ComponentInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
