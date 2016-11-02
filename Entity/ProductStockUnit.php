<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

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
    public function setSubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof Model\ProductInterface) {
            throw new InvalidArgumentException("Expected instance of ProductInterface.");
        }

        return $this->setProduct($subject);
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->getProduct();
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(Model\ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }
}
