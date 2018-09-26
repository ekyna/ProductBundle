<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Doctrine\ORM\Hydrator\PriceScalarHydrator;
use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class PriceRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceRepository extends ResourceRepository implements PriceRepositoryInterface
{
    /**
     * @var array
     */
    private $cachedCountryCodes;

    /**
     * @var Query
     */
    private $findOneByProductAndContextQuery;


    /**
     * Sets the cached country codes.
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
    public function findByProduct(ProductInterface $product, $asArray = false)
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

    /**
     * @inheritdoc
     */
    public function findOneByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        $useCache = true
    ) {
        $group = $context->getCustomerGroup();
        $country = $context->getInvoiceCountry();

        $query = $this->getOneFindByProductAndContextQuery();

        if ($useCache && $country && in_array($country->getCode(), $this->cachedCountryCodes, true)) {
            $query->useResultCache(true, null, Price::buildCacheId($product, $group, $country));
        } else {
            $query->useResultCache(false);
        }

        return $query
            ->setParameters([
                'product'  => $product,
                'group'    => $group,
                'country'  => $country,
            ])
            ->getOneOrNullResult(PriceScalarHydrator::NAME);
    }

    /**
     * Returns the "find by product and context" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getOneFindByProductAndContextQuery()
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
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     *
     * @return array
     */
    private function arrayResult(QueryBuilder $qb, array $parameters)
    {
        return $qb
            ->select([
                'p.id as id',
                'p.startingFrom as starting_from',
                'p.originalPrice as original_price',
                'p.sellPrice as sell_price',
                'p.percent as percent',
                'p.details as details',
                'IDENTITY(p.group) as group_id',
                'IDENTITY(p.country) as country_id',
            ])
            ->getQuery()
            ->setParameters($parameters)
            ->getResult(PriceScalarHydrator::NAME);
    }

    /**
     * Returns the query builder result as objects.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\Price[]
     */
    private function objectResult(QueryBuilder $qb, array $parameters)
    {
        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }
}