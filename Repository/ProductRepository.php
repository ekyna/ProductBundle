<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes as BStockModes;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as CStockModes;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepository;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends TranslatableResourceRepository implements ProductRepositoryInterface
{
    // TODO Store queries in private properties

    /**
     * @inheritdoc
     */
    public function getUpdateDateById($id, $visible = true, array $types = null)
    {
        $qb = $this->getUpdateDateQueryBuilder($visible, $types);

        $parameters = ['id' => $id];

        if ($visible) {
            $parameters = array_replace($parameters, [
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
            ]);
        }

        if (is_array($types) && !empty($types)) {
            $parameters['types'] = $types;
        }

        $query = $qb
            ->select('p.updatedAt')
            ->andWhere($qb->expr()->eq('p.id', ':id'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters($parameters)
            ->setMaxResults(1);

        if (null !== $date = $query->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR)) {
            return new \DateTime($date);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateDateBySlug($slug, $visible = true, array $types = null)
    {
        $qb = $this->getUpdateDateQueryBuilder($visible, $types);

        $parameters = [
            'slug'   => $slug,
            'locale' => $this->localeProvider->getCurrentLocale(),
        ];

        if ($visible) {
            $parameters = array_replace($parameters, [
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
            ]);
        }

        if (is_array($types) && !empty($types)) {
            $parameters['types'] = $types;
        }

        $query = $qb
            ->select('p.updatedAt')
            ->andWhere($qb->expr()->eq('translation.slug', ':slug'))
            ->andWhere($qb->expr()->eq('translation.locale', ':locale'))
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters($parameters)
            ->setMaxResults(1);

        if (null !== $date = $query->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR)) {
            return new \DateTime($date);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function findOneById($id)
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinSeo($qb)
            ->joinOptionGroups($qb, true);

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $qb
            ->andWhere($qb->expr()->eq($as . '.id', ':id'))
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'id'               => $id,
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
            ])
            ->getOneOrNullResult();

        $this->loadAssociations($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function findOneByReference($reference)
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinSeo($qb)
            ->joinOptionGroups($qb, true);

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $qb
            ->andWhere($qb->expr()->eq($as . '.reference', ':reference'))
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'reference'        => $reference,
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
            ])
            ->getOneOrNullResult();

        $this->loadAssociations($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function findOneBySlug($slug)
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinSeo($qb)
            ->joinOptionGroups($qb, true);

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $qb
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->andWhere($qb->expr()->eq('translation.slug', ':slug'))
            ->andWhere($qb->expr()->eq('translation.locale', ':locale'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
                'slug'             => $slug,
                'locale'           => $this->localeProvider->getCurrentLocale(),
            ])
            ->getOneOrNullResult();

        $this->loadAssociations($product);

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function findByBrand(Model\BrandInterface $brand)
    {
        $as = $this->getAlias();
        $qb = $this->getCollectionQueryBuilder();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinOptionGroups($qb);

        $query = $qb
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->andWhere($qb->expr()->eq($as . '.brand', ':brand'))
            ->andWhere($qb->expr()->in($as . '.type', ':types'))
            ->getQuery()
            ->useQueryCache(true);

        return $query
            ->setParameters([
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
                'brand'            => $brand,
                'types'            => [
                    Model\ProductTypes::TYPE_SIMPLE,
                    Model\ProductTypes::TYPE_VARIABLE,
                    Model\ProductTypes::TYPE_BUNDLE,
                    Model\ProductTypes::TYPE_CONFIGURABLE,
                ],
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findByCategory(Model\CategoryInterface $category, $recursive = false)
    {
        $as = $this->getAlias();
        $qb = $this->getCollectionQueryBuilder();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinOptionGroups($qb);

        $query = $qb
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->andWhere($qb->expr()->isMemberOf(':categories', $as . '.categories'))
            ->andWhere($qb->expr()->in($as . '.type', ':types'))
            ->getQuery()
            ->useQueryCache(true);

        $categories = [$category];
        if ($recursive) {
            $categories = array_merge($categories, $category->getChildren()->toArray());
        };

        return $query
            ->setParameters([
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
                'categories'       => $categories,
                'types'            => [
                    Model\ProductTypes::TYPE_SIMPLE,
                    Model\ProductTypes::TYPE_VARIABLE,
                    Model\ProductTypes::TYPE_BUNDLE,
                    Model\ProductTypes::TYPE_CONFIGURABLE,
                ],
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findParentsByBundled(Model\ProductInterface $bundled, $requiredSlots = false)
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $qb
            ->leftJoin($as . '.bundleSlots', 's')
            ->leftJoin('s.choices', 'c')
            ->andWhere($qb->expr()->eq('c.product', ':bundled'));

        if ($requiredSlots) {
            $qb->andWhere($qb->expr()->eq('s.required', true));
        }

        return $qb
            ->getQuery()
            //->useQueryCache(true)
            ->setParameter('bundled', $bundled)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findParentsByOptionProduct(Model\ProductInterface $product, $requiredGroups = false)
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $qb
            ->leftJoin($as . '.optionGroups', 'g')
            ->leftJoin('g.options', 'o')
            ->andWhere($qb->expr()->eq('o.product', ':product'));

        if ($requiredGroups) {
            $qb->andWhere($qb->expr()->eq('g.required', true));
        }

        return $qb
            ->getQuery()
            //->useQueryCache(true)
            ->setParameter('product', $product)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findOutOfStockProducts($mode)
    {
        BStockModes::isValid($mode, true);

        $qb = $this->createQueryBuilder('p');

        $today = new \DateTime();
        $today->setTime(0,0,0);

        return $qb
            ->andWhere($qb->expr()->in('p.type', ':types'))
            ->andWhere($qb->expr()->eq('p.stockMode', ':mode'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->lt('p.virtualStock', 'p.stockFloor'),
                $qb->expr()->andX(
                    $qb->expr()->isNotNull('p.estimatedDateOfArrival'),
                    $qb->expr()->lt('p.estimatedDateOfArrival', ':today')
                )
            ))
            ->getQuery()
            ->setParameter('mode', $mode)
            ->setParameter('types', [Model\ProductTypes::TYPE_SIMPLE, Model\ProductTypes::TYPE_VARIANT])
            ->setParameter('today', $today, Type::DATETIME)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findForInventoryExport()
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere($qb->expr()->in('p.type', ':types'))
            ->andWhere($qb->expr()->neq('p.stockMode', ':mode'))
            ->getQuery()
            ->setParameter('mode', [CStockModes::MODE_DISABLED])
            ->setParameter('types', [Model\ProductTypes::TYPE_SIMPLE, Model\ProductTypes::TYPE_VARIANT])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findOneByPendingOffers()
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere($qb->expr()->eq('p.pendingOffers', ':flag'))
            ->getQuery()
            ->setMaxResults(1)
            ->setParameter('flag', true)
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function loadMedias(Model\ProductInterface $product)
    {
        if (!$this->isInitializedCollection($product->getMedias())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.medias', 'pm')
                ->leftJoin('pm.media', 'm')
                ->leftJoin('m.translations', 'm_t', Expr\Join::WITH, $this->getLocaleCondition('m_t'))
                ->select('PARTIAL p.{id}', 'pm', 'm', 'm_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $product->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadOptions(Model\ProductInterface $product)
    {
        if (!$this->isInitializedCollection($product->getOptionGroups())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.optionGroups', 'og')
                ->leftJoin('og.translations', 'og_t', Expr\Join::WITH, $this->getLocaleCondition('og_t'))
                ->leftJoin('og.options', 'o')
                ->leftJoin('o.translations', 'o_t', Expr\Join::WITH, $this->getLocaleCondition('o_t'))
                ->select('PARTIAL p.{id}', 'og', 'og_t', 'o', 'o_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $product->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadVariants(Model\ProductInterface $variable)
    {
        Model\ProductTypes::assertVariable($variable);

        if (!$this->isInitializedCollection($variable->getVariants())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.variants', 'v')
                ->leftJoin('v.translations', 'v_t', Expr\Join::WITH, $this->getLocaleCondition('v_t'))
                ->select('PARTIAL p.{id}', 'v', 'v_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $variable->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadBundleSlots(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        if (!$this->isInitializedCollection($bundle->getBundleSlots())) {
            $qb = $this->createQueryBuilder('p');
            $qb
                // Slots
                ->leftJoin('p.bundleSlots', 'bs')
                ->leftJoin('bs.translations', 'bs_t', Expr\Join::WITH, $this->getLocaleCondition('bs_t'))
                // Choices
                ->leftJoin('bs.choices', 'bc')
                // Choices products
                ->leftJoin('bc.product', 'bcp')
                ->leftJoin('bcp.translations', 'bcp_t', Expr\Join::WITH, $this->getLocaleCondition('bcp_t'))
                // Choices products brands
                ->leftJoin('bcp.brand', 'bcb')
                ->leftJoin('bcb.translations', 'bcb_t', Expr\Join::WITH, $this->getLocaleCondition('bcb_t'))
                ->select('PARTIAL p.{id}', 'bs', 'bs_t', 'bc', 'bcp', 'bcp_t', 'bcb', 'bcb_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $bundle->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * @inheritdoc
     */
    public function loadConfigurableSlots(Model\ProductInterface $configurable)
    {
        Model\ProductTypes::assertConfigurable($configurable);

        if (!$this->isInitializedCollection($configurable->getBundleSlots())) {
            // Slots
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.bundleSlots', 'bs')
                ->leftJoin('bs.translations', 'bs_t', Expr\Join::WITH, $this->getLocaleCondition('bs_t'))
                ->select('PARTIAL p.{id}', 'bs', 'bs_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $configurable->getId(),
                ])
                ->getResult();

            // Slot choices
            $qb = $this->createQueryBuilder('p');
            $qb
                ->leftJoin('p.bundleSlots', 'bs')
                ->leftJoin('bs.choices', 'bc')
                ->leftJoin('bc.product', 'bcp')
                ->leftJoin('bcp.translations', 'bcp_t', Expr\Join::WITH, $this->getLocaleCondition('bcp_t'))
                ->leftJoin('bcp.brand', 'bcb')
                ->leftJoin('bcb.translations', 'bcb_t', Expr\Join::WITH, $this->getLocaleCondition('bcb_t'))
                ->select('PARTIAL p.{id}', 'PARTIAL bs.{id}', 'bc', 'bcp', 'bcp_t', 'bcb', 'bcb_t')
                ->andWhere($qb->expr()->eq('p.id', ':id'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameters([
                    'id' => $configurable->getId(),
                ])
                ->getResult();
        }
    }

    /**
     * Returns the getUpdateDateBy* query builder.
     *
     * @param bool       $visible
     * @param array|null $types
     *
     * @return QueryBuilder
     */
    private function getUpdateDateQueryBuilder($visible = true, array $types = null)
    {
        $qb = $this->getQueryBuilder('p');

        if ($visible) {
            $this
                ->joinCategories($qb)
                ->joinBrand($qb);

            $qb
                ->andWhere($qb->expr()->eq('p.visible', ':visible'))
                ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
                ->andWhere($qb->expr()->eq('c.visible', ':category_visible'));
        }

        if (is_array($types) && !empty($types)) {
            $qb->andWhere($qb->expr()->in('p.type', ':types'));
        }

        return $qb;
    }

    /**
     * Loads the product associations.
     *
     * @param Model\ProductInterface|null $product
     */
    protected function loadAssociations(Model\ProductInterface $product = null)
    {
        if (null !== $product) {
            // Medias
            $this->loadMedias($product);

            if ($product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                // Variants
                $this->loadVariants($product);
            } elseif ($product->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                // Bundle slots
                $this->loadBundleSlots($product);
            } elseif ($product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
                // Configurable slots
                $this->loadConfigurableSlots($product);
            }
        }
    }

    /**
     * Adds the join parts for brand to the query builder.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     *
     * @return ProductRepository
     */
    protected function joinBrand(QueryBuilder $qb, $alias = null)
    {
        $alias = $alias ?: $this->getAlias();

        $qb
            ->join($alias . '.brand', 'b')
            ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'))
            ->addSelect('b', 'b_t');

        return $this;
    }

    /**
     * Adds the join parts for categories to the query builder.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param bool         $withTranslations
     *
     * @return ProductRepository
     */
    protected function joinCategories(QueryBuilder $qb, $alias = null, $withTranslations = false)
    {
        $alias = $alias ?: $this->getAlias();

        $qb->leftJoin($alias . '.categories', 'c');
        if ($withTranslations) {
            $qb
                ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'))
                ->addSelect('b', 'b_t');
        }

        return $this;
    }

    /**
     * Adds the join parts for medias to the query builder.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     *
     * @return ProductRepository
     */
    protected function joinMedias(QueryBuilder $qb, $alias = null)
    {
        $alias = $alias ?: $this->getAlias();

        $qb
            ->leftJoin($alias . '.medias', 'pm')
            ->leftJoin('pm.media', 'm')
            ->leftJoin('m.translations', 'm_t', Expr\Join::WITH, $this->getLocaleCondition('m_t'))
            ->addSelect('pm', 'm', 'm_t');

        return $this;
    }

    /**
     * Adds the join parts for seo to the query builder.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     *
     * @return ProductRepository
     */
    protected function joinSeo(QueryBuilder $qb, $alias = null)
    {
        $alias = $alias ?: $this->getAlias();

        $qb
            ->leftJoin($alias . '.seo', 's')
            ->leftJoin('s.translations', 's_t', Expr\Join::WITH, $this->getLocaleCondition('s_t'))
            ->addSelect('s', 's_t');

        return $this;
    }

    /**
     * Adds the join parts for option groups to the query builder.
     *
     * @param QueryBuilder $qb
     * @param bool         $andOptions
     * @param string       $alias
     *
     * @return ProductRepository
     */
    protected function joinOptionGroups(QueryBuilder $qb, $andOptions = false, $alias = null)
    {
        $alias = $alias ?: $this->getAlias();

        $qb
            ->leftJoin($alias . '.optionGroups', 'og')
            ->addSelect('og');

        if ($andOptions) {
            $qb
                ->leftJoin('og.options', 'op')
                ->addSelect('op');
        }

        return $this;
    }

    /**
     * Returns whether the collection has been initialized or not.
     *
     * @param Collection $collection
     *
     * @return bool
     *
     * @TODO Move in a AbstractResource or ResourceUtil class ? (search 'isInitialized' usages ...)
     */
    protected function isInitializedCollection(Collection $collection = null)
    {
        return (null !== $collection)
            && method_exists($collection, 'isInitialized')
            && $collection->{'isInitialized'}();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'p';
    }
}
