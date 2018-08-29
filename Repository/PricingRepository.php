<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator\PricingGridHydrator;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class PricingRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRepository extends ResourceRepository implements PricingRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $byBrandQuery;


    /**
     * @inheritdoc
     */
    public function findRulesByBrand(BrandInterface $brand)
    {
        return $this
            ->getByBrandQuery()
            ->setParameter('brandId', $brand->getId())
            ->getScalarResult();
    }

    /**
     * Returns the "find by brand" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getByBrandQuery()
    {
        if (null !== $this->byBrandQuery) {
            return $this->byBrandQuery;
        }

        $qb = $this->createQueryBuilder('p');

        return $this->byBrandQuery = $qb
            ->select([
                'p.id as id',
                //'p.designation as designation',
                'g.id as group_id',
                'c.id as country_id',
                'r.minQuantity as min_qty',
                'r.percent as percent'
            ])
            ->join('p.groups', 'g')
            ->join('p.countries', 'c')
            ->join('p.brands', 'b')
            ->join('p.rules', 'r')
            ->addOrderBy('g.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->addOrderBy('r.minQuantity', 'DESC')
            ->where($qb->expr()->eq('b.id', ':brandId'))
            ->getQuery()
            ->useQueryCache(true);
            // TODO ->useResultCache(true, $this->getCachePrefix())
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'p';
    }
}
