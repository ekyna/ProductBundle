<?php

declare(strict_types=1);

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
    protected ?Model\ProductInterface $product = null;

    public function __clone()
    {
        parent::__clone();

        $this->product = null;
    }

    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?Model\ProductInterface $product): Model\ProductAdjustmentInterface
    {
        if ($this->product === $product) {
            return $this;
        }

        if ($previous = $this->product) {
            $this->product = null;
            $previous->removeAdjustment($this);
        }

        if ($this->product = $product) {
            $this->product->addAdjustment($this);
        }

        return $this;
    }

    public function getAdjustable(): ?AdjustableInterface
    {
        return $this->product;
    }
}
