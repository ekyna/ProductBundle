<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Entity\StatCross;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Resource\Doctrine\ORM\Util\LocaleAwareRepositoryTrait;

/**
 * Class StatCrossRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCrossRepository extends ServiceEntityRepository
{
    use LocaleAwareRepositoryTrait;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findOneQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findBestByProductAndPeriodQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findBestByProductAndPeriodAndGroupQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findByProductAndTargetAndPeriodQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findByProductAndTargetAndPeriodAndGroupQuery;


    /**
     * Constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatCross::class);
    }

    /**
     * Finds cross selling products.
     *
     * @param Product|int|array $source
     * @param Group|null        $group
     * @param \DateTime         $from
     * @param int               $limit
     * @param array             $exclude The product ids to exclude
     *
     * @return Product[]
     */
    public function findProducts(
        $source,
        Group $group = null,
        \DateTime $from = null,
        int $limit = 8,
        array $exclude = []
    ) {
        if (null === $from) {
            $from = new \DateTime('-1 year');
        }

        if ($source instanceof Product) {
            $source = [$source->getId()];
        } elseif (is_int($source)) {
            $source = [$source];
        } elseif (!is_array($source)) {
            throw new UnexpectedTypeException($source, [Product::class, 'int', 'array']);
        }

        $parameters = [
            'source'          => $source,
            'from'            => $from->format('Y-m'),
            'not_mode'        => HighlightModes::MODE_NEVER,
            'not_type'        => ProductTypes::TYPE_CONFIGURABLE,
            'not_stock_state' => StockSubjectStates::STATE_OUT_OF_STOCK,
            'visible'         => true,
        ];

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        $qb
            ->select('s as stat', 'SUM(s.count) as score', 'p', 'b', 'b_t')
            ->join('s.target', 'p')
            ->join('p.brand', 'b')
            ->join('p.categories', 'c')
            ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'))
            ->andWhere($ex->in('IDENTITY(s.source)', ':source'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->neq('p.type', ':not_type'))
            ->andWhere($ex->neq('p.stockState', ':not_stock_state'))
            ->andWhere($ex->neq('p.bestSeller', ':not_mode'))
            ->andWhere($ex->eq('p.visible', ':visible'))
            ->andWhere($ex->eq('b.visible', ':visible'))
            ->andWhere($ex->eq('c.visible', ':visible'))
            ->addGroupBy('s.target')
            ->andHaving($ex->gt('SUM(s.count)', 0))
            ->addOrderBy('score', 'DESC')
            ->addOrderBy('p.visibility', 'DESC');

        if ($group) {
            $qb->andWhere($ex->eq('s.customerGroup', ':group'));
            $parameters['group'] = $group;
        }

        if (!empty($exclude)) {
            $qb->andWhere($qb->expr()->notIn('p.id', ':exclude'));
            $parameters['exclude'] = $exclude;
        }

        $this->filterFindProducts($qb, $parameters);

        $results = $qb
            ->getQuery()
            ->setParameters($parameters)
            ->setMaxResults($limit)
            ->getResult();

        return array_map(function ($r) {
            /** @var StatCross $s */
            $s = $r['stat'];

            return $s->getTarget();
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
     * Finds one stat cross.
     *
     * @param Product $source
     * @param Product $target
     * @param Group   $group
     * @param string  $date
     *
     * @return StatCross|null
     */
    public function findOne(Product $source, Product $target, Group $group, string $date)
    {
        return $this
            ->getFindOneQuery()
            ->setParameters([
                'source' => $source,
                'target' => $target,
                'date'   => $date,
                'group'  => $group,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Finds the best cross selling products by source and period (and optionally customer group).
     *
     * @param Product     $source
     * @param \DatePeriod $period
     * @param Group|null  $group
     *
     * @return int[]
     */
    public function findBestByProductAndPeriod(Product $source, \DatePeriod $period, Group $group = null)
    {
        $parameters = [
            'source'  => $source,
            'from'    => $period->start->format('Y-m'),
            'to'      => $period->end->format('Y-m'),
            'visible' => true,
        ];

        if ($group) {
            $parameters['group'] = $group;
            $query = $this->getFindBestByProductAndPeriodAndGroupQuery();
        } else {
            $query = $this->getFindBestByProductAndPeriodQuery();
        }

        $result = $query->setParameters($parameters)->getScalarResult();

        return array_map(function ($r) {
            return (int)$r['id'];
        }, $result);
    }

    /**
     * Finds cross selling stats by source, target and period (and optionally customer group).
     *
     * @param Product     $source
     * @param Product     $target
     * @param \DatePeriod $period
     * @param Group|null  $group
     *
     * @return int[]
     */
    public function findByProductAndTargetAndPeriod(
        Product $source,
        Product $target,
        \DatePeriod $period,
        Group $group = null
    ) {
        $parameters = [
            'source' => $source,
            'target' => $target,
            'from'   => $period->start->format('Y-m'),
            'to'     => $period->end->format('Y-m'),
        ];

        if ($group) {
            $parameters['group'] = $group;
            $query = $this->getFindByProductAndTargetAndPeriodAndGroupQuery();
        } else {
            $query = $this->getFindByProductAndTargetAndPeriodQuery();
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
    private function getFindBestByProductAndPeriodQuery()
    {
        if ($this->findBestByProductAndPeriodQuery) {
            return $this->findBestByProductAndPeriodQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->findBestByProductAndPeriodQuery = $qb
            ->select('IDENTITY(s.target) as id', 'SUM(s.count) as nb')
            ->join('s.target', 't')
            ->andWhere($ex->eq('s.source', ':source'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->lte('s.date', ':to'))
            ->andWhere($ex->lte('t.visible', ':visible'))
            ->andHaving($ex->gt('SUM(s.count)', 0))
            ->addGroupBy('s.target')
            ->addOrderBy('nb', 'DESC')
            ->setMaxResults(8)// TODO Configurable
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    private function getFindBestByProductAndPeriodAndGroupQuery()
    {
        if ($this->findBestByProductAndPeriodAndGroupQuery) {
            return $this->findBestByProductAndPeriodAndGroupQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->findBestByProductAndPeriodAndGroupQuery = $qb
            ->select('IDENTITY(s.target) as id', 'SUM(s.count)as nb')
            ->join('s.target', 't')
            ->andWhere($ex->eq('s.source', ':source'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->lte('s.date', ':to'))
            ->andWhere($ex->eq('s.customerGroup', ':group'))
            ->andWhere($ex->lte('t.visible', ':visible'))
            ->andHaving($ex->gt('SUM(s.count)', 0))
            ->addGroupBy('s.target')
            ->addOrderBy('nb', 'DESC')
            ->setMaxResults(8)// TODO Configurable
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    private function getFindByProductAndTargetAndPeriodQuery()
    {
        if ($this->findByProductAndTargetAndPeriodQuery) {
            return $this->findByProductAndTargetAndPeriodQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->findByProductAndTargetAndPeriodQuery = $qb
            ->select('s.date', 'SUM(s.count) as nb')
            ->andWhere($ex->eq('s.source', ':source'))
            ->andWhere($ex->eq('s.target', ':target'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->lte('s.date', ':to'))
            ->addGroupBy('s.date')
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    private function getFindByProductAndTargetAndPeriodAndGroupQuery()
    {
        if ($this->findByProductAndTargetAndPeriodAndGroupQuery) {
            return $this->findByProductAndTargetAndPeriodAndGroupQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->findByProductAndTargetAndPeriodAndGroupQuery = $qb
            ->select('s.date', 's.count as nb')
            ->andWhere($ex->eq('s.source', ':source'))
            ->andWhere($ex->eq('s.target', ':target'))
            ->andWhere($ex->eq('s.customerGroup', ':group'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->lte('s.date', ':to'))
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
            ->andWhere($ex->eq('s.source', ':source'))
            ->andWhere($ex->eq('s.target', ':target'))
            ->andWhere($ex->eq('s.date', ':date'))
            ->andWhere($ex->eq('s.customerGroup', ':group'))
            ->getQuery()
            ->useQueryCache(true);

    }
}
