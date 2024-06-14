<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Repository\AbstractStockUnitRepository;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;

/**
 * Class ProductStockUnitRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitRepository extends AbstractStockUnitRepository
{
    protected function assertSubject(StockSubjectInterface $subject): void
    {
        if ($subject instanceof ProductInterface) {
            return;
        }

        throw new UnexpectedTypeException($subject, ProductInterface::class);
    }

    protected function getAlias(): string
    {
        return 'psu';
    }
}
