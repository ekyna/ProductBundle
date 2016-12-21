<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator\PricingGridHydrator;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class PricingRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRepository extends ResourceRepository implements PricingRepositoryInterface
{
    /**
     * Returns the pricings grid.
     *
     * @return array
     */
    public function getGrid()
    {
        $result = $this
            ->createQueryBuilder($this->getAlias())
            ->select([
                'p.id as id',
                'p.name as name',
                'g.id as group_id',
                'c.id as country_id',
                'b.id as brand_id',
                'r.minQuantity as min_quantity',
                'r.percent'
            ])
            ->join('p.groups', 'g')
            ->join('p.countries', 'c')
            ->join('p.brands', 'b')
            ->join('p.rules', 'r')
            ->addOrderBy('g.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->addOrderBy('r.minQuantity', 'DESC')
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, $this->getCachePrefix())
            ->getResult(PricingGridHydrator::NAME);

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'p';
    }
}
