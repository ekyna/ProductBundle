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
     * @var integer
     */
    protected $id;

    /**
     * @var Product
     */
    protected $product;


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;

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
