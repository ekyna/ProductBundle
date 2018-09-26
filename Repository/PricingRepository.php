<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
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
    private $byProductQuery;


    /**
     * @inheritdoc
     */
    public function findRulesByProduct(ProductInterface $product)
    {
        return $this
            ->getByProductQuery()
            ->setParameters([
                'brand'   => $product->getBrand(),
                'product' => $product->getId(),
            ])
            ->getScalarResult();
    }

    /**
     * Returns the "find by brand" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getByProductQuery()
    {
        if (null !== $this->byProductQuery) {
            return $this->byProductQuery;
        }

        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $this->byProductQuery = $qb
            ->select([
                'p.id as pricing_id',
                // TODO (?) 'p.designation as designation',
                'g.id as group_id',
                'c.id as country_id',
                'r.minQuantity as min_qty',
                'r.percent as percent',
            ])
            ->join('p.groups', 'g')
            ->join('p.countries', 'c')
            ->join('p.brands', 'b')
            ->join('p.rules', 'r')
            ->addOrderBy('g.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->addOrderBy('r.minQuantity', 'DESC')
            ->where($ex->orX(
                $ex->eq('p.product', ':product'),
                $ex->isMemberOf(':brand', 'p.brands')
            ))
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'p';
    }
}
