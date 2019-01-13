<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;

/**
 * Class StatCountRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCountRepository extends EntityRepository
{
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
     * Finds one stat count.
     *
     * @param Product $product
     * @param string  $date
     * @param Group   $group
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\StatCount|null
     */
    public function findOne(Product $product, string $date, Group $group)
    {
        return $this
            ->getFindOneQuery()
            ->setParameters([
                'product' => $product,
                'date'    => $date,
                'group'   => $group,
            ])
            ->getOneOrNullResult();
    }

    /**
     * Finds count stats by product, group and dates.
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

            $result = $this
                ->getFindByProductAndPeriodAndGroupQuery()
                ->setParameters($parameters)
                ->getScalarResult();
        } else {
            $result = $this
                ->getFindByProductAndPeriodQuery()
                ->setParameters($parameters)
                ->getScalarResult();
        }

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
            ->select(['s.date', 'SUM(s.count) as nb'])
            ->andWhere($ex->eq('s.product', ':product'))
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->lte('s.date', ':to'))
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
            ->select(['s.date', 's.count as nb'])
            ->andWhere($ex->eq('s.product', ':product'))
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
            ->andWhere($ex->eq('s.product', ':product'))
            ->andWhere($ex->eq('s.date', ':date'))
            ->andWhere($qb->expr()->eq('s.customerGroup', ':group'))
            ->getQuery()
            ->useQueryCache(true);

    }
}
