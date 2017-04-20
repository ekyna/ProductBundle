<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator\OfferScalarHydrator;
use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\CacheUtil;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class PriceRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceRepository extends ResourceRepository implements PriceRepositoryInterface
{
    private array  $cachedCountryCodes;
    private int    $cacheTtl                        = 3600;
    private ?Query $findOneByProductAndContextQuery = null;

    public function setCachedCountryCodes(array $codes): void
    {
        $this->cachedCountryCodes = $codes;
    }

    public function setCacheTtl(int $cacheTtl): void
    {
        $this->cacheTtl = $cacheTtl;
    }

    public function findByProduct(ProductInterface $product, bool $asArray = false): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->eq('p.product', ':product'))
            ->addOrderBy('p.percent', 'DESC')
            ->addOrderBy('IDENTITY(p.group)', 'DESC')
            ->addOrderBy('IDENTITY(p.country)', 'DESC');

        $parameters = [
            'product' => $product,
        ];

        return $asArray
            ? $this->arrayResult($qb, $parameters)
            : $this->objectResult($qb, $parameters);
    }

    public function findOneByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        bool             $useCache = true
    ): ?array {
        $group = $context->getCustomerGroup();
        $country = $context->getInvoiceCountry();

        $query = $this->getOneFindByProductAndContextQuery();

        if ($useCache && $country && in_array($country->getCode(), $this->cachedCountryCodes, true)) {
            $query->enableResultCache($this->cacheTtl, CacheUtil::buildPriceKey($product, $group, $country));
        } else {
            $query->disableResultCache();
        }

        return $query
            ->setParameters([
                'product' => $product,
                'group'   => $group,
                'country' => $country,
            ])
            ->getOneOrNullResult(OfferScalarHydrator::NAME);
    }

    /**
     * Returns the "find by product and context" query.
     */
    private function getOneFindByProductAndContextQuery(): Query
    {
        if (null !== $this->findOneByProductAndContextQuery) {
            return $this->findOneByProductAndContextQuery;
        }

        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $this->findOneByProductAndContextQuery = $qb
            ->select([
                'p.id as id',
                'p.startingFrom as starting_from',
                'p.originalPrice as original_price',
                'p.sellPrice as sell_price',
                'p.percent as percent',
                'p.details as details',
                'p.endsAt as ends_at',
            ])
            ->andWhere($ex->eq('p.product', ':product'))
            ->andWhere($ex->orX($ex->eq('p.group', ':group'), $ex->isNull('p.group')))
            ->andWhere($ex->orX($ex->eq('p.country', ':country'), $ex->isNull('p.country')))
            ->addOrderBy('p.percent', 'DESC')
            ->addOrderBy('IDENTITY(p.group)', 'DESC')
            ->addOrderBy('IDENTITY(p.country)', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->useQueryCache(true);
    }

    /**
     * Returns the query builder result as array.
     */
    private function arrayResult(QueryBuilder $qb, array $parameters): array
    {
        return $qb
            ->select([
                'p.id as id',
                'p.startingFrom as starting_from',
                'p.originalPrice as original_price',
                'p.sellPrice as sell_price',
                'p.percent as percent',
                'p.details as details',
                'p.endsAt as ends_at',
                'IDENTITY(p.group) as group_id',
                'IDENTITY(p.country) as country_id',
            ])
            ->getQuery()
            ->setParameters($parameters)
            ->getResult(OfferScalarHydrator::NAME);
    }

    /**
     * Returns the query builder result as objects.
     *
     * @return array<Price>
     */
    private function objectResult(QueryBuilder $qb, array $parameters): array
    {
        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }
}
