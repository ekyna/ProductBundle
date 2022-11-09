<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes as BStockModes;
use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes as CStockModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use Ekyna\Component\Resource\Doctrine\ORM\Hydrator\IdHydrator;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\TranslatableRepository;

use function array_merge;
use function array_replace;
use function array_unique;
use function is_array;
use function is_int;
use function is_null;
use function method_exists;

use const SORT_ASC;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends TranslatableRepository implements ProductRepositoryInterface
{
    /** @var array<int, Query> */
    private array $findNextQuery = [];

    // TODO Store queries in private properties

    public function getUpdateDateById(int $id, bool $visible = true, array $types = null): ?DateTimeInterface
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

        if (null !== $date = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)) {
            return new DateTime($date);
        }

        return null;
    }

    public function getUpdateDateBySlug(string $slug, bool $visible = true, array $types = null): ?DateTimeInterface
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

        if (null !== $date = $query->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)) {
            return new DateTime($date);
        }

        return null;
    }

    public function findNext(int $id, array $types = [], int $direction = SORT_ASC): ?Model\ProductInterface
    {
        if (empty($types)) {
            $types = [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT];
        }

        return $this
            ->getFindNextQuery($direction)
            ->setParameters([
                'id'    => $id,
                'types' => $types,
            ])
            ->getOneOrNullResult();
    }

    public function findOneById(int $id): ?Model\ProductInterface
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinSeo($qb);

        /** @var Model\ProductInterface $product */
        $product = $qb
            ->andWhere($qb->expr()->eq($as . '.id', ':id'))
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->enableResultCache(3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
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

    public function findOneBySlug(string $slug): ?Model\ProductInterface
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

        /** @var Model\ProductInterface $product */
        $product = $qb
            ->leftJoin($as . '.translations', 't', Expr\Join::WITH, $this->getLocaleCondition('t'))
            ->addSelect('t')
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->andWhere($qb->expr()->eq('t.slug', ':slug'))
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->enableResultCache(3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
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

    public function findOneByReference(string $reference): ?Model\ProductInterface
    {
        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb)
            ->joinSeo($qb);

        /** @var Model\ProductInterface $product */
        $product = $qb
            ->andWhere($qb->expr()->eq($as . '.reference', ':reference'))
            ->andWhere($qb->expr()->eq($as . '.visible', ':visible'))
            ->andWhere($qb->expr()->eq('b.visible', ':brand_visible'))
            ->andWhere($qb->expr()->eq('c.visible', ':category_visible'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true)
            // TODO ->enableResultCache(3600, $this->getCachePrefix() . '[slug=' . $slug . ']')
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

    public function findOneByExternalReference(
        string $code,
        array  $types = [],
        bool   $visible = true
    ): ?Model\ProductInterface {
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

    public function findByBrand(Model\BrandInterface $brand): array
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

    public function findByCategory(Model\CategoryInterface $category, bool $recursive = false): array
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
        }

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

    public function findParentsByBundled(
        Model\ProductInterface $bundled,
        bool                   $requiredSlots = false,
        bool                   $idOnly = false,
        ?int                   $limit = null
    ): array {
        if (is_null($bundled->getId())) {
            return [];
        }

        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $qb
            ->join($as . '.bundleSlots', 's')
            ->join('s.choices', 'c');

        $parameters = [];

        if (Model\ProductTypes::isVariableType($bundled)) {
            $qb->andWhere($qb->expr()->in('IDENTITY(c.product)', ':bundled'));
            $products = $bundled->getVariants()->toArray();
            $products[] = $bundled;
            $parameters['bundled'] = $this->filterProductsIds($products);
        } elseif (Model\ProductTypes::isVariantType($bundled)) {
            $qb->andWhere($qb->expr()->in('IDENTITY(c.product)', ':bundled'));
            $parameters['bundled'] = $this->filterProductsIds([$bundled, $bundled->getParent()]);
        } else {
            $qb->andWhere($qb->expr()->eq('c.product', ':bundled'));
            $parameters['bundled'] = $bundled;
        }

        if ($requiredSlots) {
            $qb->andWhere($qb->expr()->eq('s.required', ':required'));
            $parameters['required'] = true;
        }

        if ($idOnly) {
            return $qb
                ->select($as . '.id')
                ->getQuery()
                ->setMaxResults($limit)
                //->useQueryCache(true)
                ->setParameters($parameters)
                ->getResult(IdHydrator::NAME);
        }

        return $qb
            ->getQuery()
            ->setMaxResults($limit)
            //->useQueryCache(true)
            ->setParameters($parameters)
            ->getResult();
    }

    public function findParentsByOptionProduct(
        Model\ProductInterface $product,
        bool                   $requiredGroups = false,
        bool                   $idOnly = false,
        ?int                   $limit = null
    ): array {
        if (is_null($product->getId())) {
            return [];
        }

        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();

        $qb
            ->join($as . '.optionGroups', 'g')
            ->join('g.options', 'o');

        $parameters = [];

        if (Model\ProductTypes::isVariableType($product)) {
            $qb->andWhere($qb->expr()->in('IDENTITY(o.product)', ':products'));
            $products = $product->getVariants()->toArray();
            $products[] = $product;
            $parameters['products'] = $this->filterProductsIds($products);
        } elseif (Model\ProductTypes::isVariantType($product)) {
            $qb->andWhere($qb->expr()->in('IDENTITY(o.product)', ':products'));
            $parameters['products'] = $this->filterProductsIds([$product, $product->getParent()]);
        } else {
            $qb->andWhere($qb->expr()->eq('o.product', ':product'));
            $parameters['product'] = $product;
        }

        if ($requiredGroups) {
            $qb->andWhere($qb->expr()->eq('g.required', ':required'));
            $parameters['required'] = true;
        }

        if ($idOnly) {
            return $qb
                ->select($as . '.id')
                ->getQuery()
                ->setMaxResults($limit)
                //->useQueryCache(true)
                ->setParameters($parameters)
                ->getResult(IdHydrator::NAME);
        }

        return $qb
            ->getQuery()
            ->setMaxResults($limit)
            //->useQueryCache(true)
            ->setParameters($parameters)
            ->getResult();
    }

    public function findParentsByComponent(
        Model\ProductInterface $product,
        bool                   $idOnly = false,
        ?int                   $limit = null
    ): array {
        if (is_null($product->getId())) {
            return [];
        }

        $as = $this->getAlias();
        $qb = $this->getQueryBuilder();
        $qb
            ->join($as . '.components', 'c')
            ->andWhere($qb->expr()->eq('c.child', ':product'));

        if ($idOnly) {
            return $qb
                ->select($as . '.id')
                ->getQuery()
                ->setMaxResults($limit)
                ->setParameter('product', $product)
                //->useQueryCache(true)
                ->getResult(IdHydrator::NAME);
        }

        return $qb
            ->getQuery()
            ->setMaxResults($limit)
            //->useQueryCache(true)
            ->setParameter('product', $product)
            ->getResult();
    }

    public function findOutOfStockProducts(string $mode): array
    {
        BStockModes::isValid($mode, true);

        $qb = $this->createQueryBuilder('p');

        $today = new DateTime();
        $today->setTime(0, 0);

        return $qb
            ->andWhere($qb->expr()->in('p.type', ':types'))
            ->andWhere($qb->expr()->eq('p.stockMode', ':mode'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.endOfLife', 0),
                        $qb->expr()->lt('p.virtualStock', 'p.stockFloor')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.endOfLife', 1),
                        $qb->expr()->lt('p.virtualStock', 0)
                    )
                ),
                $qb->expr()->andX(
                    $qb->expr()->isNotNull('p.estimatedDateOfArrival'),
                    $qb->expr()->lte('p.estimatedDateOfArrival', ':today')
                )
            ))
            ->getQuery()
            ->setParameter('mode', $mode)
            ->setParameter('types', [Model\ProductTypes::TYPE_SIMPLE, Model\ProductTypes::TYPE_VARIANT])
            ->setParameter('today', $today, Types::DATE_MUTABLE)
            ->getResult();
    }

    /**
     * Finds products having a past estimated date of arrival.
     */
    public function findHavingPastEDA(): array
    {
        $qb = $this->createQueryBuilder('p');

        $today = new DateTime();
        $today->setTime(0, 0);

        return $qb
            ->andWhere($qb->expr()->in('p.type', ':types'))
            ->andWhere($qb->expr()->neq('p.stockMode', ':not_mode'))
            ->andWhere($qb->expr()->andX(
                $qb->expr()->isNotNull('p.estimatedDateOfArrival'),
                $qb->expr()->lt('p.estimatedDateOfArrival', ':today')
            ))
            ->getQuery()
            ->setParameter('not_mode', StockSubjectModes::MODE_DISABLED)
            ->setParameter('types', [Model\ProductTypes::TYPE_SIMPLE, Model\ProductTypes::TYPE_VARIANT])
            ->setParameter('today', $today, Types::DATE_MUTABLE)
            ->getResult();
    }

    public function findForInventoryExport(): array
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

    public function findOneByPendingOffers(string $type): ?Model\ProductInterface
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

    public function findOneByPendingPrices(string $type): ?Model\ProductInterface
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

    public function findDuplicateByReference(
        Model\ProductInterface $product,
        array                  $ignore = []
    ): ?Model\ProductInterface {
        if (empty($reference = $product->getReference())) {
            return null;
        }

        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->eq('p.reference', ':reference'))
            ->setParameter('reference', $reference);

        $ignore[] = $product;
        if (!empty($ids = $this->filterProductsIds($ignore))) {
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

    public function findDuplicateByDesignationAndBrand(
        Model\ProductInterface $product,
        array                  $ignore = []
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

        $ignore[] = $product;
        if (!empty($ids = $this->filterProductsIds($ignore))) {
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

    public function findForSitemap(): array
    {
        $qb = $this->getQueryBuilder('p');

        return $qb
            ->innerJoin('p.seo', 's')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('c.translations', 'c_t', Expr\Join::WITH, $this->getLocaleCondition('c_t'))
            ->innerJoin('p.brand', 'b')
            ->addSelect('s', 'c', 'c_t')
            ->andWhere($qb->expr()->eq('p.visible', true))
            ->andWhere($qb->expr()->eq('b.visible', true))
            ->andWhere($qb->expr()->eq('c.visible', true))
            ->andWhere($qb->expr()->eq('s.index', true))
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filters the products ids.
     *
     * @return int[]
     */
    private function filterProductsIds(array $ignore): array
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

    public function loadMedias(Model\ProductInterface $product): void
    {
        if ($this->isInitializedCollection($product->getMedias())) {
            return;
        }

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

    public function loadOptions(Model\ProductInterface $product): void
    {
        if ($this->isInitializedCollection($product->getOptionGroups())) {
            return;
        }

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

    public function loadVariants(Model\ProductInterface $variable): void
    {
        Model\ProductTypes::assertVariable($variable);

        if ($this->isInitializedCollection($variable->getVariants())) {
            return;
        }

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

    public function loadBundleSlots(Model\ProductInterface $bundle): void
    {
        Model\ProductTypes::assertBundle($bundle);

        if ($this->isInitializedCollection($bundle->getBundleSlots())) {
            return;
        }

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

    public function loadConfigurableSlots(Model\ProductInterface $configurable): void
    {
        Model\ProductTypes::assertConfigurable($configurable);

        if ($this->isInitializedCollection($configurable->getBundleSlots())) {
            return;
        }

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

    public function findNextStatUpdate(DateTimeInterface $maxDate = null): ?Model\ProductInterface
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
            $query->setParameter('max_date', $maxDate, Types::DATETIME_MUTABLE);
        }

        return $query
            ->getOneOrNullResult();
    }

    public function findBestSellers(array $options = []): array
    {
        return $this->findHighlight('bestSeller', $options);
    }

    public function findCrossSelling(array $options = []): array
    {
        return $this->findHighlight('crossSelling', $options);
    }

    public function findForHighlight(): array
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->select([
                'p.id',
                'b.name as brand',
                'p.designation',
                'p.reference',
                'p.visible',
                'p.visibility',
                'p.bestSeller',
                'p.crossSelling',
            ])
            ->join('p.brand', 'b')
            ->where($qb->expr()->neq('p.type', ':not_type'))
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->setParameter('not_type', ProductTypes::TYPE_VARIANT)
            ->getScalarResult();
    }

    public function findForExport(ExportConfig $config): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->join('p.brand', 'b')
            ->addOrderBy('b.name', 'ASC')
            ->addOrderBy('p.designation', 'ASC')
            ->andWhere($qb->expr()->notIn('p.type', ':types'))
            ->andWhere($qb->expr()->eq('p.quoteOnly', ':quote_only'))
            ->andWhere(
                $qb->expr()->not(
                    $qb->expr()->andX(
                        $qb->expr()->eq('p.endOfLife', ':end_of_life'),
                        $qb->expr()->neq('p.stockState', ':stock_state')
                    )
                )
            )
            ->setParameters(
                [
                    'types'       => [
                        ProductTypes::TYPE_VARIANT,
                        ProductTypes::TYPE_CONFIGURABLE,
                    ],
                    'quote_only'  => false,
                    'end_of_life' => true,
                    'stock_state' => StockSubjectStates::STATE_IN_STOCK,
                ]
            );

        $brands = $config->getBrands();
        if (!$brands->isEmpty()) {
            $qb
                ->andWhere($qb->expr()->in('p.brand', ':brands'))
                ->setParameter('brands', $brands->toArray());
        }

        if ($config->isVisible()) {
            $qb
                ->andWhere($qb->expr()->eq('p.visible', ':visible'))
                ->setParameter('visible', true);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    protected function findHighlight(string $type, array $options): array
    {
        Model\HighlightModes::isValidType($type);

        $options = array_replace([
            'limit'   => 4,
            'exclude' => [],
            'id_only' => false,
        ], $options);

        $parameters = [
            'mode'        => Model\HighlightModes::MODE_ALWAYS,
            'type'        => Model\ProductTypes::TYPE_CONFIGURABLE,
            'stock_state' => StockSubjectStates::STATE_OUT_OF_STOCK,
            'visible'     => true,
            'quote_only'  => false,
            'end_of_life' => false,
        ];

        $as = $this->getAlias();
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        $this
            ->joinCategories($qb)
            ->joinBrand($qb);

        if ($options['id_only']) {
            $qb->select($as . '.id');
        }

        $qb
            ->andWhere($ex->eq($as . '.' . $type, ':mode'))
            ->andWhere($ex->neq($as . '.type', ':type'))
            ->andWhere($ex->neq($as . '.stockState', ':stock_state'))
            ->andWhere($ex->eq($as . '.visible', ':visible'))
            ->andWhere($ex->eq('b.visible', ':visible'))
            ->andWhere($ex->eq('c.visible', ':visible'))
            ->andWhere($ex->eq($as . '.quoteOnly', ':quote_only'))
            ->andWhere($ex->eq($as . '.endOfLife', ':end_of_life'))
            ->addOrderBy('p.visibility', 'DESC');

        if (!empty($options['exclude'])) {
            $qb->andWhere($ex->notIn($as . '.id', ':exclude'));
            $parameters['exclude'] = $options['exclude'];
        }

        $this->filterFindHighlight($qb, $parameters, $type);

        $query = $qb
            ->getQuery()
            ->setParameters($parameters)
            ->setMaxResults($options['limit']);

        if ($options['id_only']) {
            return $query->getResult(IdHydrator::NAME);
        }

        return $query->getResult();
    }

    /**
     * Apply custom filtering to findHighlight method.
     *
     * @param QueryBuilder $qb
     * @param array        $parameters
     * @param string       $type
     */
    protected function filterFindHighlight(QueryBuilder $qb, array &$parameters, string $type): void
    {
    }

    /**
     * Returns the getUpdateDateBy* query builder.
     */
    private function getUpdateDateQueryBuilder(bool $visible = true, array $types = null): QueryBuilder
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
     */
    protected function loadAssociations(?Model\ProductInterface $product): void
    {
        if (null === $product) {
            return;
        }

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

    public function joinPrice(QueryBuilder $qb, string $alias = null): ProductRepositoryInterface
    {
        $alias = $alias ?: $this->getAlias();

        $ex = $qb->expr();

        $qb2 = $this->wrapped->createQueryBuilder('p');
        $qb2
            ->select('pri.sellPrice')
            ->from(Price::class, 'pri')
            ->andWhere($ex->eq('IDENTITY(pri.product)', $alias . '.id'))
            ->andWhere($ex->orX($ex->eq('pri.group', ':customer_group'), $ex->isNull('pri.group')))
            ->andWhere($ex->orX($ex->eq('pri.country', ':invoice_country'), $ex->isNull('pri.country')));

        $qb->addSelect("IFNULL(({$qb2->getQuery()->getDQL()}), $alias.minPrice) AS sellPrice");

        return $this;
    }

    /**
     * Adds the join parts for brand to the query builder.
     */
    protected function joinBrand(
        QueryBuilder $qb,
        string       $alias = null,
        bool         $withTranslations = true
    ): ProductRepositoryInterface {
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
     */
    protected function joinCategories(
        QueryBuilder $qb,
        string       $alias = null,
        bool         $withTranslations = false
    ): ProductRepositoryInterface {
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
     */
    protected function joinMedias(QueryBuilder $qb, string $alias = null): ProductRepositoryInterface
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
     */
    protected function joinSeo(QueryBuilder $qb, string $alias = null): ProductRepositoryInterface
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
     */
    protected function joinOptionGroups(
        QueryBuilder $qb,
        bool         $andOptions = false,
        string       $alias = null
    ): ProductRepositoryInterface {
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

    private function getFindNextQuery(int $direction): Query
    {
        if (isset($this->findNextQuery[$direction])) {
            return $this->findNextQuery[$direction];
        }

        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere($qb->expr()->in('p.type', ':types'))
            ->setMaxResults(1);

        if (SORT_ASC === $direction) {
            $qb
                ->andWhere($qb->expr()->gt('p.id', ':id'))
                ->orderBy('p.id', 'ASC');
        } else {
            $qb
                ->andWhere($qb->expr()->lt('p.id', ':id'))
                ->orderBy('p.id', 'DESC');
        }

        return $this->findNextQuery[$direction] = $qb->getQuery();
    }

    /**
     * Returns whether the collection has been initialized or not.
     *
     * @TODO Move in a AbstractResource or ResourceUtil class ? (search 'isInitialized' usages ...)
     */
    protected function isInitializedCollection(?Collection $collection): bool
    {
        return (null !== $collection)
            && method_exists($collection, 'isInitialized')
            && $collection->{'isInitialized'}();
    }

    protected function getAlias(): string
    {
        return 'p';
    }
}
