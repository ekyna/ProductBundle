<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Entity\AbstractAdjustment;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;

/**
 * Class ProductAdjustment
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAdjustment extends AbstractAdjustment implements Model\ProductAdjustmentInterface
{
    /**
     * @var Model\ProductInterface
     */
    protected $product;


    /**
     * @inheritdoc
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->product = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(Model\ProductInterface $product = null): Model\ProductAdjustmentInterface
    {
        if ($this->product !== $product) {
            if ($previous = $this->product) {
                $this->product = null;
                $previous->removeAdjustment($this);
            }

            if ($this->product = $product) {
                $this->product->addAdjustment($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustable(): ?AdjustableInterface
    {
        return $this->product;
    }
}
