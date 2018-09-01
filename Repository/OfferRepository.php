<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class OfferRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferRepository extends ResourceRepository implements OfferRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $findByProductAndContextQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findOneByProductAndContextAndQuantityQuery;

    /**
     * @var array
     */
    private $cachedCountryCodes;

    /**
     * Sets the cachedCountryCodes.
     *
     * @param array $codes
     */
    public function setCachedCountryCodes($codes)
    {
        $this->cachedCountryCodes = $codes;
    }

    /**
     * @inheritdoc
     */
    public function findByProductAndContext(
        Model\ProductInterface $product,
        ContextInterface $context,
        $useCache = true
    ) {
        $group = $context->getCustomerGroup();
        $country = $context->getInvoiceCountry();

        $query = $this->getFindByProductAndContextQuery();

        if ($useCache && $country && in_array($country->getCode(), $this->cachedCountryCodes, true)) {
            $query->useResultCache(true, null, Offer::buildCacheId($product, $group, $country));
        } else {
            $query->useResultCache(false);
        }

        $offers = $query
            ->setParameters([
                'product' => $product,
                'group'   => $group,
                'country' => $country,
            ])
            ->getResult(Query::HYDRATE_SCALAR);

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

    /**
     * @inheritdoc
     */
    public function findOneByProductAndContextAndQuantity(
        Model\ProductInterface $product,
        ContextInterface $context,
        $quantity = 1.0,
        $useCache = true
    ) {
        $group = $context->getCustomerGroup();
        $country = $context->getInvoiceCountry();
        $quantity = intval($quantity);

        $query = $this->getOneFindByProductAndContextAndQuantityQuery();

        if ($useCache && (1 === $quantity) && $country && in_array($country->getCode(), $this->cachedCountryCodes, true)) {
            $query->useResultCache(true, null, Offer::buildCacheId($product, $group, $country, $quantity, false));
        } else {
            $query->useResultCache(false);
        }

        return $query
            ->setParameters([
                'product'  => $product,
                'group'    => $group,
                'country'  => $country,
                'quantity' => $quantity,
            ])
            ->getOneOrNullResult(Query::HYDRATE_SCALAR);
    }

    /**
     * @inheritdoc
     */
    public function findByProduct(Model\ProductInterface $product, $asArray = false)
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->andWhere($qb->expr()->eq('o.product', ':product'))
            ->addOrderBy('o.percent', 'DESC')
            ->addOrderBy('o.minQuantity', 'DESC')
            ->addOrderBy('IDENTITY(o.group)', 'DESC')
            ->addOrderBy('IDENTITY(o.country)', 'DESC');

        $parameters = [
            'product' => $product,
        ];

        return $asArray
            ? $this->scalarResult($qb, $parameters)
            : $this->objectResult($qb, $parameters);
    }

    /**
     * Returns the "find by product and context" uery.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getFindByProductAndContextQuery()
    {
        if (null !== $this->findByProductAndContextQuery) {
            return $this->findByProductAndContextQuery;
        }

        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $this->findByProductAndContextQuery = $qb
            ->select([
                //'o.id as id',
                // TODO 'p.designation as designation',
                //'IDENTITY(o.group) as group_id',
                //'IDENTITY(o.country) as country_id',
                'o.minQuantity as min_qty',
                'o.percent as percent',
                'o.netPrice as price',
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
     *
     * @return \Doctrine\ORM\Query
     */
    private function getOneFindByProductAndContextAndQuantityQuery()
    {
        if (null !== $this->findOneByProductAndContextAndQuantityQuery) {
            return $this->findOneByProductAndContextAndQuantityQuery;
        }

        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $this->findOneByProductAndContextAndQuantityQuery = $qb
            ->select([
                // TODO 'p.designation as designation',
                'o.percent as percent',
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
     * Returns the query builder result as scalar.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     *
     * @return Offer[]
     */
    private function scalarResult(QueryBuilder $qb, array $parameters)
    {
        return $qb
            ->select([
                'o.id as id',
                // TODO 'p.designation as designation',
                'IDENTITY(o.group) as group_id',
                'IDENTITY(o.country) as country_id',
                'o.minQuantity as min_qty',
                'o.percent as percent',
                'o.netPrice as net_price',
                'IDENTITY(o.specialOffer) as special_offer_id',
                'IDENTITY(o.pricing) as pricing_id',
            ])
            ->getQuery()
            ->setParameters($parameters)
            ->getScalarResult();
    }

    /**
     * Returns the query builder result as objects.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     *
     * @return Offer[]
     */
    private function objectResult(QueryBuilder $qb, array $parameters)
    {
        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }
}
