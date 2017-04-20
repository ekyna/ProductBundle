<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
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
    protected ?Model\ProductInterface $product = null;


    public function setProduct(Model\ProductInterface $product): Model\ProductStockUnitInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    public function setSubject(?StockSubjectInterface $subject): StockUnitInterface
    {
        if ($subject && !$subject instanceof Model\ProductInterface) {
            throw new UnexpectedTypeException($subject, Model\ProductInterface::class);
        }

        return $this->setProduct($subject);
    }

    public function getSubject(): ?StockSubjectInterface
    {
        return $this->getProduct();
    }
}
