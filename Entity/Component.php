<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\ComponentInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class Component
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Component extends AbstractResource implements ComponentInterface
{
    private ?ProductInterface $parent   = null;
    private ?ProductInterface $child    = null;
    private Decimal           $quantity;
    private ?Decimal          $netPrice = null;

    public function __construct()
    {
        $this->quantity = new Decimal(1);
    }

    public function __toString(): string
    {
        return null !== $this->child ? (string)$this->child : 'New component';
    }

    public function __clone()
    {
        parent::__clone();

        $this->parent = null;
    }

    public function getParent(): ?ProductInterface
    {
        return $this->parent;
    }

    public function setParent(?ProductInterface $parent): ComponentInterface
    {
        if ($this->parent === $parent) {
            return $this;
        }

        if ($previous = $this->parent) {
            $this->parent = null;
            $previous->removeComponent($this);
        }

        if ($this->parent = $parent) {
            $this->parent->addComponent($this);
        }

        return $this;
    }

    public function getChild(): ?ProductInterface
    {
        return $this->child;
    }

    public function setChild(?ProductInterface $child): ComponentInterface
    {
        $this->child = $child;

        return $this;
    }

    public function getQuantity(): Decimal
    {
        return $this->quantity;
    }

    public function setQuantity(?Decimal $quantity): ComponentInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getNetPrice(): ?Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(?Decimal $price): ComponentInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
