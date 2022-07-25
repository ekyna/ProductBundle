<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query;
use Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator\OfferScalarHydrator;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

use function in_array;

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
        if (in_array($product->getType(), [ProductTypes::TYPE_VARIABLE, ProductTypes::TYPE_CONFIGURABLE], true)) {
            throw new InvalidArgumentException('Expected simple, variant or bundle product type.');
        }

        return $this
            ->getByProductQuery()
            ->setParameters([
                'brand'         => $product->getBrand(),
                'pricing_group' => $product->getPricingGroup(),
                'product'       => $product,
            ])
            ->getResult(OfferScalarHydrator::NAME);
    }

    public function findByContext(ContextInterface $context): array
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->isNull('p.product'))
            ->andWhere($ex->orX(
                'p.customerGroups IS EMPTY',
                $ex->isMemberOf(':customer_group', 'p.customerGroups')
            ))
            ->andWhere($ex->orX(
                'p.countries IS EMPTY',
                $ex->isMemberOf(':country', 'p.countries')
            ))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters([
                'customer_group' => $context->getCustomerGroup(),
                'country'        => $context->getInvoiceCountry(),
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
                'cg.id as group_id',
                'c.id as country_id',
                'r.minQuantity as min_qty',
                'r.percent as percent',
            ])
            ->join('p.rules', 'r')
            ->leftJoin('p.customerGroups', 'cg')
            ->leftJoin('p.countries', 'c')
//            ->leftJoin('p.brands', 'b')
//            ->leftJoin('p.pricingGroups', 'pg')
            ->addOrderBy('cg.id', 'ASC')
            ->addOrderBy('c.id', 'ASC')
//            ->addOrderBy('b.id', 'ASC')
//            ->addOrderBy('pg.id', 'ASC')
            ->addOrderBy('r.percent', 'DESC')
            ->addOrderBy('r.minQuantity', 'DESC')
            ->where($ex->orX(
                $ex->eq('p.product', ':product'),
                $ex->isMemberOf(':brand', 'p.brands'),
                $ex->isMemberOf(':pricing_group', 'p.pricingGroups')
            ))
            ->getQuery()
            ->useQueryCache(true);
    }

    protected function getAlias(): string
    {
        return 'p';
    }
}
