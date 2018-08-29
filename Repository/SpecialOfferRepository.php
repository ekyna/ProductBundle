<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\DBAL\Types\Type;
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
            ->setParameter('now', new \DateTime(), Type::DATE)
            ->getScalarResult();
    }

    /**
     * Returns special offers starting today or ending yesterday.
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface[]
     */
    public function findStartingTodayOrEndingYesterday()
    {
        $today = new \DateTime();
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
            ->setParameter('today', $today, Type::DATE)
            ->setParameter('yesterday', $yesterday, Type::DATE)
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
                'p.id as id',
                // TODO 'p.designation as designation',
                'g.id as group_id',
                'c.id as country_id',
                'p.minQuantity as min_qty',
                'p.percent as percent',
            ])
            ->leftJoin('p.groups', 'g')
            ->leftJoin('p.countries', 'c')
            ->leftJoin('p.brands', 'b')
            ->addOrderBy('g.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->addOrderBy('p.minQuantity', 'DESC')
            ->andWhere($ex->eq('p.enabled', ':enabled'))
            ->andWhere($ex->orX(
                $ex->isMemberOf(':brand', 'p.brands'),
                $ex->isMemberOf(':product', 'p.products')
            ))
            ->andWhere($ex->orX($ex->isNull('p.startsAt'), $ex->lte('p.startsAt', ':now')))
            ->andWhere($ex->orX($ex->isNull('p.endsAt'), $ex->gte('p.endsAt', ':now')))
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
