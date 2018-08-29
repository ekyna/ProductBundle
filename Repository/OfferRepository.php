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
class OfferRepository extends ResourceRepository
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $findByProductAndContextQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $findByProductAndContextAndQuantityQuery;

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
     * Find offers by product, context and quantity.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     * @param bool                   $useCache
     *
     * @return array
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

        return $query
            ->setParameters([
                'product' => $product,
                'group'   => $group,
                'country' => $country,
            ])
            ->getResult(Query::HYDRATE_SCALAR);
    }

    /**
     * Find offers by product, context and quantity.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     * @param float                  $quantity
     * @param bool                   $useCache
     *
     * @return array
     */
    public function findByProductAndContextAndQuantity(
        Model\ProductInterface $product,
        ContextInterface $context,
        $quantity = 1.0,
        $useCache = true
    ) {
        $group = $context->getCustomerGroup();
        $country = $context->getInvoiceCountry();
        $quantity = intval($quantity);

        $query = $this->getFindByProductAndContextAndQuantityQuery();

        if ($useCache && 1 === $quantity && $country && in_array($country->getCode(), $this->cachedCountryCodes, true)) {
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
     * Finds offers by product.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $asArray
     *
     * @return Offer[]|array[]
     */
    public function findByProduct(Model\ProductInterface $product, $asArray = false)
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->andWhere($qb->expr()->eq('o.product', ':product'))
            ->addOrderBy('IDENTITY(o.group)', 'DESC')
            ->addOrderBy('IDENTITY(o.country)', 'DESC')
            ->addOrderBy('o.minQuantity', 'DESC')
            ->addOrderBy('o.percent', 'DESC');

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
                'o.id as id',
                // TODO 'p.designation as designation',
                'o.minQuantity as quantity',
                'o.percent as percent',
            ])
            ->andWhere($ex->eq('o.product', ':product'))
            ->andWhere($ex->orX($ex->eq('o.group', ':group'), $ex->isNull('o.group')))
            ->andWhere($ex->orX($ex->eq('o.country', ':country'), $ex->isNull('o.country')))
            ->addOrderBy('IDENTITY(o.group)', 'DESC')
            ->addOrderBy('IDENTITY(o.country)', 'DESC')
            ->addOrderBy('o.minQuantity', 'DESC')
            ->addOrderBy('o.percent', 'DESC')
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * Returns the "find by product, context and quantity" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getFindByProductAndContextAndQuantityQuery()
    {
        if (null !== $this->findByProductAndContextAndQuantityQuery) {
            return $this->findByProductAndContextAndQuantityQuery;
        }

        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $this->findByProductAndContextAndQuantityQuery = $qb
            ->select([
                // TODO 'p.designation as designation',
                'o.percent as percent',
            ])
            ->andWhere($ex->eq('o.product', ':product'))
            ->andWhere($ex->orX($ex->eq('o.group', ':group'), $ex->isNull('o.group')))
            ->andWhere($ex->orX($ex->eq('o.country', ':country'), $ex->isNull('o.country')))
            ->andWhere($ex->lte('o.minQuantity', ':quantity'))
            ->addOrderBy('IDENTITY(o.group)', 'DESC')
            ->addOrderBy('IDENTITY(o.country)', 'DESC')
            ->addOrderBy('o.minQuantity', 'DESC')
            ->addOrderBy('o.percent', 'DESC')
            ->getQuery()
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
