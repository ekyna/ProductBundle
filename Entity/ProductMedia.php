<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\MediaBundle\Model\GalleryMediaTrait;
use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class ProductMedia
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductMedia implements ProductMediaInterface
{
    use GalleryMediaTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Product
     */
    protected $product;


    /**
     * Clones the product media.
     */
    public function __clone()
    {
        $this->id = null;
        $this->product = null;
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(ProductInterface $product = null)
    {
        if ($this->product !== $product) {
            if ($previous = $this->product) {
                $this->product = null;
                $previous->removeMedia($this);
            }

            if ($this->product = $product) {
                $this->product->addMedia($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }
}
