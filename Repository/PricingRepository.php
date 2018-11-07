<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
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
    public function findRulesByProduct(Model\ProductInterface $product)
    {
        return $this
            ->getByProductQuery()
            ->setParameters([
                'brand'   => $product->getBrand(),
                'product' => $product,
            ])
            ->getScalarResult();
    }

    /**
     * @inheritdoc
     */
    public function findByContext(ContextInterface $context)
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->isNull('p.product'))
            ->andWhere($ex->orX(
                'p.groups IS EMPTY',
                $ex->isMemberOf(':group', 'p.groups')
            ))
            ->andWhere($ex->orX(
                'p.countries IS EMPTY',
                $ex->isMemberOf(':country', 'p.countries')
            ))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters([
                'group' => $context->getCustomerGroup(),
                'country' => $context->getInvoiceCountry(),
            ])
            ->getResult();
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
            ->join('p.rules', 'r')
            ->leftJoin('p.groups', 'g')
            ->leftJoin('p.countries', 'c')
            ->leftJoin('p.brands', 'b')
            ->addOrderBy('g.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->addOrderBy('r.percent', 'DESC')
            ->addOrderBy('r.minQuantity', 'DESC')
            ->addGroupBy('r.id')
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
