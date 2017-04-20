<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\MediaBundle\Model\GalleryMediaTrait;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;

/**
 * Class ProductMedia
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductMedia implements ProductMediaInterface
{
    use GalleryMediaTrait;

    protected ?int              $id      = null;
    protected ?ProductInterface $product = null;

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

    public function setProduct(?ProductInterface $product): ProductMediaInterface
    {
        if ($this->product === $product) {
            return $this;
        }

        if ($previous = $this->product) {
            $this->product = null;
            $previous->removeMedia($this);
        }

        if ($this->product = $product) {
            $this->product->addMedia($this);
        }

        return $this;
    }
}
