<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use DateTime;
use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

use function round;

/**
 * Class SpecialOfferRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferRepository extends ResourceRepository implements SpecialOfferRepositoryInterface
{
    private ?Query $byProductQuery = null;

    public function findRulesByProduct(ProductInterface $product): array
    {
        $rules = $this
            ->getByProductQuery()
            ->setParameters([
                'product' => $product,
                'brand'   => $product->getBrand(),
                'now'     => new DateTime(),
                'enabled' => true,
            ])
            ->setParameter('now', new DateTime(), Types::DATE_MUTABLE)
            ->getScalarResult();

        return array_map(function ($rule) {
            return [
                'special_offer_id' => (int)$rule['special_offer_id'],
                'group_id'         => $rule['group_id'] ? (int)$rule['group_id'] : null,
                'country_id'       => $rule['country_id'] ? (int)$rule['country_id'] : null,
                'min_qty'          => new Decimal($rule['min_qty']),
                'percent'          => new Decimal($rule['percent']),
                'stack'            => (bool)$rule['stack'],
            ];
        }, $rules);
    }

    public function findStartingTodayOrEndingYesterday(): array
    {
        $today = new DateTime();
        $yesterday = new DateTime('-1 day');

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
     */
    private function getByProductQuery(): Query
    {
        if (null !== $this->byProductQuery) {
            return $this->byProductQuery;
        }

        $qb = $this->createQueryBuilder('s');
        $ex = $qb->expr();

        return $this->byProductQuery = $qb
            ->select([
                's.id as special_offer_id',
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

    protected function getAlias(): string
    {
        return 's';
    }
}
