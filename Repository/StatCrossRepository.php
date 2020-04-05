<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Entity\StatCross;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;
use Ekyna\Component\Resource\Doctrine\ORM\Util\LocaleAwareRepositoryTrait;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class StatCrossRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCrossRepository extends AbstractStatRepository
{
    use LocaleAwareRepositoryTrait;


    /**
     * @var Query
     */
    private $findOneQuery;

    /**
     * @var Query
     */
    private $findBestByProductAndPeriodQuery;

    /**
     * @var Query
     */
    private $findBestByProductAndPeriodAndGroupQuery;

    /**
     * @var Query
     */
    private $findByProductAndTargetAndPeriodQuery;

    /**
     * @var Query
     */
    private $findByProductAndTargetAndPeriodAndGroupQuery;


    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatCross::class);
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
    public function findOne(Product $source, Product $target, Group $group, string $date): ?StatCross
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
    public function findBestByProductAndPeriod(Product $source, \DatePeriod $period, Group $group = null): array
    {
        $parameters = [
            'source'  => $source,
            'from'    => $period->start->format('Y-m'),
            'to'      => $period->end->format('Y-m'),
            'visible' => true,
        ];

        if ($group) {
            $parameters['group'] = $group;
            $query               = $this->getFindBestByProductAndPeriodAndGroupQuery();
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
    ): array {
        $parameters = [
            'source' => $source,
            'target' => $target,
            'from'   => $period->start->format('Y-m'),
            'to'     => $period->end->format('Y-m'),
        ];

        if ($group) {
            $parameters['group'] = $group;
            $query               = $this->getFindByProductAndTargetAndPeriodAndGroupQuery();
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
     * @inheritDoc
     */
    public function getFindProductsDefaultParameters(): array
    {
        return array_replace(parent::getFindProductsDefaultParameters(), [
            'source' => null,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getFindProductsResults(Query $query, array $parameters): array
    {
        if ($parameters['id_only']) {
            return array_column($query->getScalarResult(), 'pid');
        }

        return array_map(function ($r) {
            /** @var StatCross $s */
            $s = $r['stat'];

            return $s->getTarget();
        }, $query->getResult());
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
            ->join('s.target', 'p')
            ->addGroupBy('s.target')
            ->andWhere($qb->expr()->in('IDENTITY(s.source)', ':source'))
            ->andWhere($qb->expr()->eq('p.crossSelling', ':mode'));
    }

    /**
     * Configures the "find products" query builder.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     */
    protected function configureFindProductsQueryBuilder(QueryBuilder $qb, array $parameters): void
    {
        $qb
            ->setParameter('source', $parameters['source'])
            ->setParameter('mode', HighlightModes::MODE_AUTO);
    }

    /**
     * Configures the "find products" parameters resolver.
     *
     * @param OptionsResolver $resolver
     *
     * @noinspection PhpUnusedParameterInspection*/
    protected function configureFindProductsParametersResolver(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('source')
            ->setAllowedTypes('source', [Product::class, 'int', 'array'])
            ->setNormalizer('source', function (Options $options, $source) {
                if (!is_array($source)) {
                    $source = [$source];
                }

                return array_map(function ($s) {
                    if ($s instanceof Product) {
                        return $s->getId();
                    }
                    if (is_int($s)) {
                        return $s;
                    }
                    throw new InvalidOptionsException(
                        "Invalid option 'source': expected ProductInterface, integer or array of those."
                    );
                }, $source);
            });
    }

    /**
     * @return Query
     */
    private function getFindBestByProductAndPeriodQuery(): Query
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
     * @return Query
     */
    private function getFindBestByProductAndPeriodAndGroupQuery(): Query
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
     * @return Query
     */
    private function getFindByProductAndTargetAndPeriodQuery(): Query
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
     * @return Query
     */
    private function getFindByProductAndTargetAndPeriodAndGroupQuery(): Query
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
            ->andWhere($ex->eq('s.source', ':source'))
            ->andWhere($ex->eq('s.target', ':target'))
            ->andWhere($ex->eq('s.date', ':date'))
            ->andWhere($ex->eq('s.customerGroup', ':group'))
            ->getQuery()
            ->useQueryCache(true);

    }
}
