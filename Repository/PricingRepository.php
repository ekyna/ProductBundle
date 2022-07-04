<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Decimal\Decimal;
use Doctrine\ORM\Query;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

use function array_map;

/**
 * Class PricingRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRepository extends ResourceRepository implements PricingRepositoryInterface
{
    private ?Query $byProductQuery = null;

    public function findRulesByProduct(ProductInterface $product): array
    {
        $rules = $this
            ->getByProductQuery()
            ->setParameters([
                'brand'   => $product->getBrand(),
                'product' => $product,
            ])
            ->getScalarResult();

        return array_map(function ($rule) {
            return [
                'pricing_id' => (int)$rule['pricing_id'],
                'group_id'   => $rule['group_id'] ? (int)$rule['group_id'] : null,
                'country_id' => $rule['country_id'] ? (int)$rule['country_id'] : null,
                'min_qty'    => new Decimal($rule['min_qty']),
                'percent'    => new Decimal($rule['percent']),
            ];
        }, $rules);
    }

    public function findByContext(ContextInterface $context): array
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
                'group'   => $context->getCustomerGroup(),
                'country' => $context->getInvoiceCountry(),
            ])
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
            ->where($ex->orX(
                $ex->eq('p.product', ':product'),
                $ex->isMemberOf(':brand', 'p.brands')
            ))
            ->getQuery()
            ->useQueryCache(true);
    }

    protected function getAlias(): string
    {
        return 'p';
    }
}
