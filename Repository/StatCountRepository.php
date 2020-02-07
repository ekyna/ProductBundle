<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;
use Ekyna\Component\Resource\Doctrine\ORM\Util\LocaleAwareRepositoryTrait;

/**
 * Class StatCountRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCountRepository extends AbstractStatRepository
{
    use LocaleAwareRepositoryTrait;


    /**
     * @var Query
     */
    private $findOneQuery;

    /**
     * @var Query
     */
    private $findByProductAndPeriodQuery;

    /**
     * @var Query
     */
    private $findByProductAndPeriodAndGroupQuery;


    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatCount::class);
    }

    /**
     * Finds one stat count.
     *
     * @param Product $product
     * @param Group   $group
     * @param string  $date
     *
     * @return StatCount|null
     */
    public function findOne(Product $product, Group $group, string $date): ?StatCount
    {
        return $this
            ->getFindOneQuery()
            ->setParameters([
                'product' => $product,
                'group'   => $group,
                'date'    => $date,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Finds count stats by product and period (and optionally customer group).
     *
     * @param Product     $product
     * @param \DatePeriod $period
     * @param Group       $group
     *
     * @return int[]
     */
    public function findByProductAndPeriodAndGroup(Product $product, \DatePeriod $period, Group $group = null): array
    {
        $parameters = [
            'product' => $product,
            'from'    => $period->start->format('Y-m'),
            'to'      => $period->end->format('Y-m'),
        ];

        if ($group) {
            $parameters['group'] = $group;
            $query               = $this->getFindByProductAndPeriodAndGroupQuery();
        } else {
            $query = $this->getFindByProductAndPeriodQuery();
        }

        $result = $query->setParameters($parameters)->getScalarResult();

        $return = [];
        foreach ($result as $r) {
            $return[$r['date']] = $r['nb'];
        }

        return $return;
    }

    /**
     * Creates the "find products" query builder.
     *
     * @return QueryBuilder
     */
    protected function createFindProductsQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s');

        return $qb
            ->join('s.product', 'p')
            ->addGroupBy('s.product')
            ->andWhere($qb->expr()->neq('p.bestSeller', ':not_mode'));
    }

    /**
     * Configures the "find products" query builder.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     */
    protected function configureFindProductsQueryBuilder(QueryBuilder $qb, array $parameters): void
    {
        $qb->setParameter('not_mode', HighlightModes::MODE_NEVER);
    }

    /**
     * @return Query
     */
    private function getFindByProductAndPeriodQuery(): Query
    {
        if ($this->findByProductAndPeriodQuery) {
            return $this->findByProductAndPeriodQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->findByProductAndPeriodQuery = $qb
            ->select('s.date', 'SUM(s.count) as nb')
            ->andWhere($ex->eq('s.product', ':product'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->lte('s.date', ':to'))
            ->andHaving($ex->gt('SUM(s.count)', 0))
            ->addGroupBy('s.date')
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return Query
     */
    private function getFindByProductAndPeriodAndGroupQuery(): Query
    {
        if ($this->findByProductAndPeriodAndGroupQuery) {
            return $this->findByProductAndPeriodAndGroupQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->findByProductAndPeriodAndGroupQuery = $qb
            ->select('s.date', 's.count as nb')
            ->andWhere($ex->eq('s.product', ':product'))
            ->andWhere($ex->eq('s.customerGroup', ':group'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->lte('s.date', ':to'))
            ->andHaving($ex->gt('SUM(s.count)', 0))
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return Query
     */
    private function getFindOneQuery(): Query
    {
        if ($this->findOneQuery) {
            return $this->findOneQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->findOneQuery = $qb
            ->andWhere($ex->eq('s.product', ':product'))
            ->andWhere($ex->eq('s.date', ':date'))
            ->andWhere($ex->eq('s.customerGroup', ':group'))
            ->getQuery()
            ->useQueryCache(true);

    }
}
