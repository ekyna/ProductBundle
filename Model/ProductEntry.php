<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Class ProductEntry
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEntry
{
    private ?ProductInterface $product  = null;
    private ?int              $position = null;

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(ProductInterface $product = null): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
