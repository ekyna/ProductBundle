<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\MediaBundle\Model\GalleryMediaTrait;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class ProductMedia
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductMedia extends AbstractResource implements ProductMediaInterface
{
    use GalleryMediaTrait;

    protected ?ProductInterface $product = null;

    public function __clone()
    {
        parent::__clone();

        $this->product = null;
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
