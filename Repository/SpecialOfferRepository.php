<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\DBAL\Types\Types;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class SpecialOfferRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferRepository extends ResourceRepository implements SpecialOfferRepositoryInterface
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
                'product' => $product,
                'brand'   => $product->getBrand(),
                'now'     => new \DateTime(),
                'enabled' => true,
            ])
            ->setParameter('now', new \DateTime(), Types::DATE_MUTABLE)
            ->getScalarResult();
    }

    /**
     * Returns special offers starting today or ending yesterday.
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface[]
     */
    public function findStartingTodayOrEndingYesterday()
    {
        $today     = new \DateTime();
        $yesterday = new \DateTime('-1 day');

        $qb = $this->createQueryBuilder('s');

        return $qb
            ->andWhere($qb->expr()->eq('s.enabled', ':enabled'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('s.startsAt', ':today'),
                $qb->expr()->eq('s.endsAt', ':yesterday')
            ))
            ->getQuery()
            ->setParameter('enabled', true)
            ->setParameter('today', $today, Types::DATE_MUTABLE)
            ->setParameter('yesterday', $yesterday, Types::DATE_MUTABLE)
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

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->byProductQuery = $qb
            ->select([
                's.id as special_offer_id',
                // TODO (?) 's.designation as designation',
                'g.id as group_id',
                'c.id as country_id',
                's.minQuantity as min_qty',
                's.percent as percent',
                's.stack as stack',
            ])
            ->leftJoin('s.groups', 'g')
            ->leftJoin('s.countries', 'c')
            ->leftJoin('s.brands', 'b')
            ->addOrderBy('g.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->addOrderBy('s.percent', 'DESC')
            ->addOrderBy('s.minQuantity', 'DESC')
            ->andWhere($ex->eq('s.enabled', ':enabled'))
            ->andWhere($ex->orX(
                $ex->eq('s.product', ':product'),
                $ex->isMemberOf(':brand', 's.brands'),
                $ex->isMemberOf(':product', 's.products')
            ))
            ->andWhere($ex->orX($ex->isNull('s.startsAt'), $ex->lte('s.startsAt', ':now')))
            ->andWhere($ex->orX($ex->isNull('s.endsAt'), $ex->gte('s.endsAt', ':now')))
            ->addGroupBy('s.id')
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 's';
    }
}
