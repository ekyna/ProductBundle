<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes as BStockModes;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as CStockModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
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
            ->joinSeo($qb);

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

        if (null !== $product) {
            $this->loadOptions($product);
            $this->loadAssociations($product);
        }

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function findOneBySlug($slug)
    {
        $as = $this->getAlias();
        $qb = $this
            ->getQueryBuilder()
            ->resetDQLPart('join')
            ->resetDQLPart('select')
            ->select($as);

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinSeo($qb);

        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $qb
            ->leftJoin($as . '.translations', 't', Expr\Join::WITH, $this->getLocaleCondition('t'))
            ->addSelect('t')
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->andWhere($qb->expr()->eq('t.slug', ':slug'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->useResultCache(true, 3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
            ->setParameters([
                'visible'          => true,
                'brand_visible'    => true,
                'category_visible' => true,
                'slug'             => $slug,
            ])
            ->getOneOrNullResult();

        if (null !== $product) {
            $this->loadOptions($product);
            $this->loadAssociations($product);
        }

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
            ->joinSeo($qb);

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

        if (null !== $product) {
            $this->loadOptions($product);
            $this->loadAssociations($product);
        }

        return $product;
    }

    /**
     * Finds one product by external reference.
     *
     * @param string   $code    The product reference code
     * @param string[] $types   To filter references types
     * @param bool     $visible Whether to fetch visible products only
     *
     * @return Model\ProductInterface|null
     */
    public function findOneByExternalReference(string $code, array $types = [], bool $visible = true)
    {
        foreach ($types as $type) {
            Model\ProductReferenceTypes::isValid($type);
        }

        $qb = $this->getQueryBuilder('p');
        $qb
            ->join('p.references', 'r')
            ->andWhere($qb->expr()->eq('r.code', ':code'));

        $parameters = [
            'code' => $code,
        ];

        if ($visible) {
            $this
                ->joinCategories($qb)
                ->joinBrand($qb);

            $qb
                ->andWhere($qb->expr()->eq('p.visible', ':visible'))
                ->andWhere($qb->expr()->eq('b.visible', ':visible'))
                ->andWhere($qb->expr()->eq('c.visible', ':visible'));

            $parameters['visible'] = true;
        }

        if (!empty($types)) {
            $qb->andWhere($qb->expr()->in('r.type', ':types'));
            $parameters['types'] = $types;
        }

        return $qb
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters($parameters)
            ->getOneOrNullResult();
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
            ->joinBrand($qb);

        $query = $qb
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->andWhere($qb->expr()->eq($as . '.brand', ':brand'))
            ->andWhere($qb->expr()->in($as . '.type', ':types'))
            ->addOrderBy($as . '.visibility', 'DESC')
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
            ->joinBrand($qb);

        $query = $qb
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->andWhere($qb->expr()->isMemberOf(':categories', $as . '.categories'))
            ->andWhere($qb->expr()->in($as . '.type', ':types'))
            ->addOrderBy($as . '.visibility', 'DESC')
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
        if (is_null($bundled->getId())) {
            return [];
        }

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
        if (is_null($product->getId())) {
            return [];
        }

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
    public function findParentsByComponent(Model\ProductInterface $product)
    {
        if (is_null($product->getId())) {
            return [];
        }

        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();
        $qb
            ->leftJoin($as . '.components', 'c')
            ->andWhere($qb->expr()->eq('c.child', ':product'));

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
        $today->setTime(0, 0, 0, 0);

        return $qb
            ->andWhere($qb->expr()->in('p.type', ':types'))
            ->andWhere($qb->expr()->eq('p.stockMode', ':mode'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->lte('p.virtualStock', 'p.stockFloor'),
                $qb->expr()->andX(
                    $qb->expr()->isNotNull('p.estimatedDateOfArrival'),
                    $qb->expr()->lte('p.estimatedDateOfArrival', ':today')
                )
            ))
            ->getQuery()
            ->setParameter('mode', $mode)
            ->setParameter('types', [Model\ProductTypes::TYPE_SIMPLE, Model\ProductTypes::TYPE_VARIANT])
            ->setParameter('today', $today, Type::DATE)
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
            ->useQueryCache(true)
            ->setParameter('mode', [CStockModes::MODE_DISABLED])
            ->setParameter('types', [Model\ProductTypes::TYPE_SIMPLE, Model\ProductTypes::TYPE_VARIANT])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findOneByPendingOffers(string $type)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere($qb->expr()->eq('p.type', ':type'))
            ->andWhere($qb->expr()->eq('p.pendingOffers', ':flag'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters([
                'type' => $type,
                'flag' => true,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function findOneByPendingPrices(string $type)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->andWhere($qb->expr()->eq('p.type', ':type'))
            ->andWhere($qb->expr()->eq('p.pendingPrices', ':flag'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters([
                'type' => $type,
                'flag' => true,
            ])
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findDuplicateByReference(
        Model\ProductInterface $product,
        array $ignore = []
    ): ?Model\ProductInterface {
        if (empty($reference = $product->getReference())) {
            return null;
        }

        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->eq('p.reference', ':reference'))
            ->setParameter('reference', $reference);

        array_push($ignore, $product);
        if (!empty($ids = $this->ignoreToIds($ignore))) {
            $qb
                ->andWhere($qb->expr()->notIn('p.id', ':ids'))
                ->setParameter('ids', $ids);
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findDuplicateByDesignationAndBrand(
        Model\ProductInterface $product,
        array $ignore = []
    ): ?Model\ProductInterface {
        if (empty($designation = $product->getDesignation())) {
            return null;
        }
        if (is_null($brand = $product->getBrand())) {
            return null;
        }

        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->eq('p.designation', ':designation'))
            ->andWhere($qb->expr()->eq('p.brand', ':brand'))
            ->setParameter('designation', $designation)
            ->setParameter('brand', $brand);

        array_push($ignore, $product);
        if (!empty($ids = $this->ignoreToIds($ignore))) {
            $qb
                ->andWhere($qb->expr()->notIn('p.id', ':ids'))
                ->setParameter('ids', $ids);
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findBySkuOrReferences(string $code): array
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $qb
            ->leftJoin('p.references', 'r')
            ->where($ex->orX(
                $ex->eq('p.reference', ':code'),
                $ex->eq('r.code', ':code')
            ))
            ->getQuery()
            ->setParameter('code', $code)
            ->getResult();
    }

    /**
     * @param array $ignore
     *
     * @return array
     */
    private function ignoreToIds(array $ignore): array
    {
        $ids = [];

        foreach ($ignore as $id) {
            if ($id instanceof Model\ProductInterface) {
                if (null === $id = $id->getId()) {
                    continue;
                }
            }

            if (!is_int($id)) {
                throw new UnexpectedTypeException($id, ['int', Model\ProductInterface::class]);
            }

            $ids[] = $id;
        }

        return array_unique($ids);
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
     * @inheritdoc
     */
    public function findNextStatUpdate(\DateTime $maxDate = null)
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        $qb
            ->andWhere($ex->eq('p.endOfLife', ':endOfLife'))
            ->orderBy('p.statUpdatedAt', 'ASC');

        if (!is_null($maxDate)) {
            $qb->andWhere($ex->orX(
                $ex->isNull('p.statUpdatedAt'),
                $ex->lt('p.statUpdatedAt', ':max_date')
            ));
        }

        $query = $qb
            ->getQuery()
            ->setMaxResults(1)
            ->setParameter('endOfLife', false);

        if (!is_null($maxDate)) {
            $query->setParameter('max_date', $maxDate, Type::DATETIME);
        }

        return $query
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function findBestSellers(int $limit = 8, array $exclude = [])
    {
        return $this->findHighlight('bestSeller', $limit, $exclude);
    }

    /**
     * @inheritdoc
     */
    public function findCrossSelling(int $limit = 8, array $exclude = [])
    {
        return $this->findHighlight('crossSelling', $limit, $exclude);
    }

    /**
     * Returns the highlighted products.
     *
     * @param string $type
     * @param int    $limit
     * @param array  $exclude
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface[]
     */
    protected function findHighlight(string $type, int $limit, array $exclude = [])
    {
        Model\HighlightModes::isValidType($type);

        $parameters = [
            'mode'        => Model\HighlightModes::MODE_ALWAYS,
            'type'        => Model\ProductTypes::TYPE_CONFIGURABLE,
            'stock_state' => StockSubjectStates::STATE_OUT_OF_STOCK,
            'visible'     => true,
        ];

        $as = $this->getAlias();
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb);

        $qb
            ->andWhere($ex->eq($as . '.' . $type, ':mode'))
            ->andWhere($ex->neq($as . '.type', ':type'))
            ->andWhere($ex->neq($as . '.stockState', ':stock_state'))
            ->andWhere($ex->eq($as . '.visible', ':visible'))
            ->andWhere($ex->eq('b.visible', ':visible'))
            ->andWhere($ex->eq('c.visible', ':visible'))
            ->addOrderBy('p.visibility', 'DESC');

        if (!empty($exclude)) {
            $qb->andWhere($ex->notIn($as . '.id', ':exclude'));
            $parameters['exclude'] = $exclude;
        }

        $this->filterFindHighlight($qb, $parameters, $type);

        return $qb
            ->getQuery()
            ->setParameters($parameters)
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * Apply custom filtering to findHighlight method.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     * @param string       $type
     */
    protected function filterFindHighlight(QueryBuilder $qb, array &$parameters, string $type)
    {

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
     * @param bool         $withTranslations
     *
     * @return ProductRepository
     */
    protected function joinBrand(QueryBuilder $qb, $alias = null, $withTranslations = true)
    {
        $alias = $alias ?: $this->getAlias();

        $qb->join($alias . '.brand', 'b');

        if ($withTranslations) {
            $qb
                ->leftJoin('b.translations', 'b_t', Expr\Join::WITH, $this->getLocaleCondition('b_t'))
                ->addSelect('b', 'b_t');
        }

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
                ->leftJoin('c.translations', 'c_t', Expr\Join::WITH, $this->getLocaleCondition('c_t'))
                ->addSelect('c', 'c_t');
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
