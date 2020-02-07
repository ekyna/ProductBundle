<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Resource\Doctrine\ORM\Util\LocaleAwareRepositoryTrait;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractStatRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStatRepository extends ServiceEntityRepository
{
    use LocaleAwareRepositoryTrait;

    /**
     * @var OptionsResolver
     */
    protected $findProductsOptionsResolver;


    /**
     * Finds best sellers products.
     *
     * @param array $parameters
     *
     * @return Product[]
     */
    public function findProducts(array $parameters): array
    {
        $parameters = $this->getFindProductsParametersResolver()->resolve($parameters);

        $qb = $this->createFindProductsQueryBuilder();
        $ex = $qb->expr();

        $qb
            ->select('s as stat', '(SUM(s.count) * p.netPrice) as score')
            ->join('p.brand', 'b')
            ->join('p.categories', 'c')
            ->andWhere($ex->gte('s.date', ':from'))
            ->andWhere($ex->in('p.type', ':types'))
            ->andWhere($ex->neq('p.stockState', ':not_stock_state'))
            ->andWhere($ex->eq('p.quoteOnly', ':quote_only'))
            ->andWhere($ex->eq('p.endOfLife', ':end_of_life'))
            ->andWhere($ex->eq('p.visible', ':visible'))
            ->andWhere($ex->eq('b.visible', ':visible'))
            ->andWhere($ex->eq('c.visible', ':visible'))
            ->andHaving($ex->gt('score', 0))
            ->addOrderBy('score', 'DESC')
            ->addOrderBy('p.visibility', 'DESC');

        $qb
            ->setMaxResults($parameters['limit'])
            ->setParameters([
                'from'            => $parameters['from'],
                'types'           => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIABLE,
                    ProductTypes::TYPE_BUNDLE,
                ],
                'not_stock_state' => StockSubjectStates::STATE_OUT_OF_STOCK,
                'visible'         => true,
                'quote_only'      => false,
                'end_of_life'     => false,
            ]);

        if ($parameters['group']) {
            $qb
                ->andWhere($qb->expr()->eq('s.customerGroup', ':group'))
                ->setParameter('group', $parameters['group']);
        }

        if (!empty($parameters['exclude'])) {
            $qb
                ->andWhere($qb->expr()->notIn('p.id', ':exclude'))
                ->setParameter('exclude', $parameters['exclude']);
        }

        if ($parameters['id_only']) {
            $qb->addSelect('p.id as pid');
        } else {
            $qb
                ->addSelect('p', 'b', 'p_t', 'b_t')
                ->leftJoin('p.translations', 'p_t', Expr\Join::WITH, $this->getLocaleCondition('p_t'))
                ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'));
        }

        $this->configureFindProductsQueryBuilder($qb, $parameters);

        return $this->getFindProductsResults($qb->getQuery(), $parameters);
    }

    /**
     * Creates the "find products" query builder.
     *
     * @return QueryBuilder
     */
    abstract protected function createFindProductsQueryBuilder(): QueryBuilder;

    /**
     * Returns the "find products" default parameters.
     *
     * @return array
     */
    public function getFindProductsDefaultParameters(): array
    {
        return [
            'group'   => null,
            'from'    => '-6 months',
            'limit'   => 10,
            'exclude' => [],
            'id_only' => false,
        ];
    }

    /**
     * Returns the 'find products' query results.
     *
     * @param Query $query
     * @param array $parameters
     *
     * @return array
     */
    protected function getFindProductsResults(Query $query, array $parameters): array
    {
        if ($parameters['id_only']) {
            return array_column($query->getScalarResult(), 'pid');
        }

        return array_map(function ($r) {
            /** @var StatCount $s */
            $s = $r['stat'];

            return $s->getProduct();
        }, $query->getResult());
    }

    /**
     * Configures the "find products" query builder.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     */
    protected function configureFindProductsQueryBuilder(QueryBuilder $qb, array $parameters): void
    {

    }

    /**
     * Configures the "find products" parameters resolver.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureFindProductsParametersResolver(OptionsResolver $resolver): void
    {

    }

    /**
     * Returns the best sellers options resolver.
     *
     * @return OptionsResolver
     */
    protected function getFindProductsParametersResolver(): OptionsResolver
    {
        if ($this->findProductsOptionsResolver) {
            return $this->findProductsOptionsResolver;
        }

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults($this->getFindProductsDefaultParameters())
            ->setAllowedTypes('group', [Group::class, 'null'])
            ->setAllowedTypes('from', [\DateTime::class, 'string'])
            ->setAllowedTypes('limit', 'int')
            ->setAllowedTypes('exclude', 'array')
            ->setAllowedTypes('id_only', 'bool')
            ->setNormalizer('from', function(Options $options, $from) {
                if (is_string($from)) {
                    try {
                        $from = new \DateTime($from);
                    } catch (\Exception $e) {
                        throw new InvalidOptionsException("Option 'from' is invalid: date expected.");
                    }
                }

                return $from->format('Y-m');
            })
            ->setNormalizer('exclude', function (Options $options, $exclude) {
                return array_map(function($e) {
                    if (is_int($e)) {
                        return $e;
                    }
                    throw new InvalidOptionsException(
                        "Invalid option 'exclude': expected array of intergers."
                    );
                }, $exclude);
            });

        $this->configureFindProductsParametersResolver($resolver);

        return $this->findProductsOptionsResolver = $resolver;
    }
}
