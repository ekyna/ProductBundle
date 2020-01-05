<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Resource\Doctrine\ORM\Util\LocaleAwareRepositoryTrait;

/**
 * Class StatCountRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCountRepository extends ServiceEntityRepository
{
    use LocaleAwareRepositoryTrait;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findOneQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findByProductAndPeriodQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findByProductAndPeriodAndGroupQuery;


    /**
     * Constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatCount::class);
    }

    /**
     * Finds best sellers products.
     *
     * @param Group|null $group
     * @param \DateTime  $from
     * @param int        $limit
     * @param array      $exclude
     *
     * @return Product[]
     */
    public function findProducts(Group $group = null, \DateTime $from = null, int $limit = 8, array $exclude = [])
    {
        if (null === $from) {
            $from = new \DateTime('-1 year');
        }

        $parameters = [
            'from'        => $from->format('Y-m'),
            'mode'        => HighlightModes::MODE_NEVER,
            'type'        => ProductTypes::TYPE_CONFIGURABLE,
            'stock_state' => StockSubjectStates::STATE_OUT_OF_STOCK,
            'visible'     => true,
        ];

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        $qb
            ->select('s as stat', 'SUM(s.count) as count_sum', 'p', 'b', 'b_t')
            ->join('s.product', 'p')
            ->join('p.brand', 'b')
            ->join('p.categories', 'c')
            ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->neq('p.type', ':type'))
            ->andWhere($ex->neq('p.stockState', ':stock_state'))
            ->andWhere($ex->neq('p.bestSeller', ':mode'))
            ->andWhere($ex->eq('p.visible', ':visible'))
            ->andWhere($ex->eq('b.visible', ':visible'))
            ->andWhere($ex->eq('c.visible', ':visible'))
            ->addGroupBy('s.product')
            ->andHaving($ex->gt('SUM(s.count)', 0))
            ->addOrderBy('count_sum', 'DESC')
            ->addOrderBy('p.visibility', 'DESC');

        if ($group) {
            $qb->andWhere($ex->eq('s.customerGroup', ':group'));
            $parameters['group'] = $group;
        }

        if (!empty($exclude)) {
            $qb->andWhere($ex->notIn('p.id', ':exclude'));
            $parameters['exclude'] = $exclude;
        }

        $this->filterFindProducts($qb, $parameters);

        $results = $qb
            ->getQuery()
            ->setParameters($parameters)
            ->setMaxResults($limit)
            ->getResult();

        return array_map(function ($r) {
            /** @var StatCount $s */
            $s = $r['stat'];

            return $s->getProduct();
        }, $results);
    }

    /**
     * Applies custom filtering to findProducts() method.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     */
    protected function filterFindProducts(QueryBuilder $qb, array &$parameters)
    {

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
    public function findOne(Product $product, Group $group, string $date)
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
     * @return array
     */
    public function findByProductAndPeriodAndGroup(Product $product, \DatePeriod $period, Group $group = null)
    {
        $parameters = [
            'product' => $product,
            'from'    => $period->start->format('Y-m'),
            'to'      => $period->end->format('Y-m'),
        ];

        if ($group) {
            $parameters['group'] = $group;
            $query = $this->getFindByProductAndPeriodAndGroupQuery();
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
     * @return \Doctrine\ORM\Query
     */
    private function getFindByProductAndPeriodQuery()
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
     * @return \Doctrine\ORM\Query
     */
    private function getFindByProductAndPeriodAndGroupQuery()
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
     * @return \Doctrine\ORM\Query
     */
    private function getFindOneQuery()
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
