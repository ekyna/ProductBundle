<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class ProductStockUnit
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnit extends AbstractStockUnit implements Model\ProductStockUnitInterface
{
    /**
     * @var Model\ProductInterface
     */
    protected $product;


    /**
     * @inheritdoc
     */
    public function setProduct(Model\ProductInterface $product): Model\ProductStockUnitInterface
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct(): Model\ProductInterface
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setSubject(StockSubjectInterface $subject): StockUnitInterface
    {
        if (!$subject instanceof Model\ProductInterface) {
            throw new InvalidArgumentException("Expected instance of ProductInterface.");
        }

        return $this->setProduct($subject);
    }

    /**
     * @inheritdoc
     */
    public function getSubject(): ?StockSubjectInterface
    {
        return $this->getProduct();
    }
}
