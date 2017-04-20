<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Decimal\Decimal;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator\OfferScalarHydrator;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\CacheUtil;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class OfferRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferRepository extends ResourceRepository implements OfferRepositoryInterface
{
    private array  $cachedCountryCodes;
    private int    $cacheTtl                                   = 3600;
    private ?Query $findByProductAndContextQuery               = null;
    private ?Query $findOneByProductAndContextAndQuantityQuery = null;

    public function setCachedCountryCodes(array $codes): void
    {
        $this->cachedCountryCodes = $codes;
    }

    public function setCacheTtl(int $cacheTtl): void
    {
        $this->cacheTtl = $cacheTtl;
    }

    public function findByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        bool             $useCache = true
    ): array {
        $group = $context->getCustomerGroup();
        $country = $context->getInvoiceCountry();

        // TODO Use ekyna_product.cache ?

        $query = $this->getFindByProductAndContextQuery();

        if ($useCache && $this->isCachedCountry($country)) {
            $query->enableResultCache($this->cacheTtl, CacheUtil::buildOfferKey($product, $group, $country));
        } else {
            $query->disableResultCache();
        }

        $offers = $query
            ->setParameters([
                'product' => $product,
                'group'   => $group,
                'country' => $country,
            ])
            ->getResult(OfferScalarHydrator::NAME);

        // Remove worst offers
        // TODO Should be done by the query :s
        foreach ($offers as $ak => $ad) {
            foreach ($offers as $bk => $bd) {
                if ($ak == $bk) {
                    continue;
                }

                if ($ad['min_qty'] == $bd['min_qty'] && $ad['percent'] > $bd['percent']) {
                    unset($offers[$bk]);
                }
            }
        }

        return $offers;
    }

    public function findOneByProductAndContextAndQuantity(
        ProductInterface $product,
        ContextInterface $context,
        Decimal          $quantity = null,
        bool             $useCache = true
    ): ?array {
        $group = $context->getCustomerGroup();
        $country = $context->getInvoiceCountry();
        $quantity = $quantity ?: new Decimal(1);

        $query = $this->getOneFindByProductAndContextAndQuantityQuery();

        if ($useCache && $quantity->equals(1) && $this->isCachedCountry($country)) {
            $key = CacheUtil::buildOfferKey($product, $group, $country, $quantity, false);
            $query->enableResultCache($this->cacheTtl, $key);
        } else {
            $query->disableResultCache();
        }

        return $query
            ->setParameters([
                'product'  => $product,
                'group'    => $group,
                'country'  => $country,
                'quantity' => $quantity,
            ])
            ->getOneOrNullResult(OfferScalarHydrator::NAME);
    }

    /**
     * Returns whether the given country is cached.
     */
    private function isCachedCountry(?CountryInterface $country): bool
    {
        return $country && in_array($country->getCode(), $this->cachedCountryCodes, true);
    }

    public function findByProduct(ProductInterface $product, bool $asArray = false): array
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->andWhere($qb->expr()->eq('o.product', ':product'))
            ->addOrderBy('IDENTITY(o.group)', 'DESC')
            ->addOrderBy('IDENTITY(o.country)', 'DESC')
            ->addOrderBy('o.percent', 'DESC')
            ->addOrderBy('o.minQuantity', 'DESC');

        $parameters = [
            'product' => $product,
        ];

        return $asArray
            ? $this->arrayResult($qb, $parameters)
            : $this->objectResult($qb, $parameters);
    }

    /**
     * Returns the "find by product and context" query.
     */
    private function getFindByProductAndContextQuery(): Query
    {
        if (null !== $this->findByProductAndContextQuery) {
            return $this->findByProductAndContextQuery;
        }

        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $this->findByProductAndContextQuery = $qb
            ->select([
                'o.minQuantity as min_qty',
                'o.percent as percent',
                'o.netPrice as net_price',
            ])
            ->andWhere($ex->eq('o.product', ':product'))
            ->andWhere($ex->orX($ex->eq('o.group', ':group'), $ex->isNull('o.group')))
            ->andWhere($ex->orX($ex->eq('o.country', ':country'), $ex->isNull('o.country')))
            ->addOrderBy('o.percent', 'DESC')
            ->addOrderBy('o.minQuantity', 'DESC')
            ->addOrderBy('IDENTITY(o.group)', 'DESC')
            ->addOrderBy('IDENTITY(o.country)', 'DESC')
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * Returns the "find by product, context and quantity" query.
     */
    private function getOneFindByProductAndContextAndQuantityQuery(): Query
    {
        if (null !== $this->findOneByProductAndContextAndQuantityQuery) {
            return $this->findOneByProductAndContextAndQuantityQuery;
        }

        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $this->findOneByProductAndContextAndQuantityQuery = $qb
            ->select([
                'o.percent as percent',
                'o.netPrice as net_price',
                'IDENTITY(o.specialOffer) as special_offer_id',
                'IDENTITY(o.pricing) as pricing_id',
            ])
            ->andWhere($ex->eq('o.product', ':product'))
            ->andWhere($ex->orX($ex->eq('o.group', ':group'), $ex->isNull('o.group')))
            ->andWhere($ex->orX($ex->eq('o.country', ':country'), $ex->isNull('o.country')))
            ->andWhere($ex->lte('o.minQuantity', ':quantity'))
            ->addOrderBy('o.percent', 'DESC')
            ->addOrderBy('o.minQuantity', 'DESC')
            ->addOrderBy('IDENTITY(o.group)', 'DESC')
            ->addOrderBy('IDENTITY(o.country)', 'DESC')
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
                'o.id as id',
                'IDENTITY(o.group) as group_id',
                'IDENTITY(o.country) as country_id',
                'o.minQuantity as min_qty',
                'o.percent as percent',
                'o.netPrice as net_price',
                'o.details as details',
                'IDENTITY(o.specialOffer) as special_offer_id',
                'IDENTITY(o.pricing) as pricing_id',
            ])
            ->getQuery()
            ->setParameters($parameters)
            ->getResult(OfferScalarHydrator::NAME);
    }

    /**
     * Returns the query builder result as objects.
     *
     * @return array<Offer>
     */
    private function objectResult(QueryBuilder $qb, array $parameters): array
    {
        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }
}
