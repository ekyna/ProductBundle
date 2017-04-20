<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceInterface;

/**
 * Class ProductReference
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReference implements ProductReferenceInterface
{
    protected ?int              $id      = null;
    protected ?ProductInterface $product = null;
    protected ?string           $type    = null;
    protected ?string           $code    = null;

    public function __clone()
    {
        $this->id = null;
        $this->product = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): ProductReferenceInterface
    {
        if ($this->product === $product) {
            return $this;
        }
        if ($previous = $this->product) {
            $this->product = null;
            $previous->removeReference($this);
        }

        if ($this->product = $product) {
            $this->product->addReference($this);
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): ProductReferenceInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): ProductReferenceInterface
    {
        $this->code = $code;

        return $this;
    }
}
