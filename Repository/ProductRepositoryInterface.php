<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\CategoryInterface;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface ProductRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<ProductInterface>
 */
interface ProductRepositoryInterface extends TranslatableRepositoryInterface, SubjectRepositoryInterface
{
    /**
     * Returns the product update date by its id.
     */
    public function getUpdateDateById(int $id, bool $visible = true, array $types = null): ?DateTimeInterface;

    /**
     * Returns the product update date by its slug.
     */
    public function getUpdateDateBySlug(string $slug, bool $visible = true, array $types = null): ?DateTimeInterface;

    /**
     * Finds the product by id (if product and its brand and categories are visible).
     */
    public function findOneById(int $id): ?ProductInterface;

    /**
     * Finds the product by slug (if product and its brand and categories are visible).
     */
    public function findOneBySlug(string $slug): ?ProductInterface;

    /**
     * Finds the product by reference (if product and its brand and categories are visible).
     */
    public function findOneByReference(string $reference): ?ProductInterface;

    /**
     * Finds one product by external reference.
     *
     * @param string   $code    The product reference code
     * @param string[] $types   To filter references types
     * @param bool     $visible Whether to fetch visible products only
     */
    public function findOneByExternalReference(
        string $code,
        array  $types = [],
        bool   $visible = true
    ): ?ProductInterface;

    /**
     * Finds products by brand.
     *
     * @return array<ProductInterface>
     */
    public function findByBrand(BrandInterface $brand): array;

    /**
     * Finds products by category, optionally including children categories.
     *
     * @return array<ProductInterface>
     */
    public function findByCategory(CategoryInterface $category, bool $recursive = false): array;

    /**
     * Finds the parents products of the given bundled product.
     *
     * @param bool $requiredSlots Whether to return only bundles having this product as a REQUIRED slot choice.
     *
     * @return array<ProductInterface|int>
     */
    public function findParentsByBundled(
        ProductInterface $bundled,
        bool             $requiredSlots = false,
        bool             $idOnly = false,
        ?int             $limit = null
    ): array;

    /**
     * Finds the products having the given product as option.
     *
     * @param bool $requiredGroups Whether to return only bundles having this product as a REQUIRED option choice.
     *
     * @return array<ProductInterface|int>
     */
    public function findParentsByOptionProduct(
        ProductInterface $product,
        bool             $requiredGroups = false,
        bool             $idOnly = false,
        ?int             $limit = null
    ): array;

    /**
     * Finds the products having the given product as component.
     *
     * @return array<ProductInterface|int>
     */
    public function findParentsByComponent(
        ProductInterface $product,
        bool             $idOnly = false,
        ?int             $limit = null
    ): array;

    /**
     * Finds the "out of stock" products for the given mode.
     *
     * @return array<ProductInterface>
     */
    public function findOutOfStockProducts(string $mode): array;

    /**
     * Finds products having a past estimated date of arrival.
     *
     * @return array<ProductInterface>
     */
    public function findHavingPastEDA(): array;

    /**
     * Finds the products for inventory export.
     *
     * @return array<ProductInterface>
     */
    public function findForInventoryExport(): array;

    /**
     * Finds one product with "pending offers" flag set to true.
     */
    public function findOneByPendingOffers(string $type): ?ProductInterface;

    /**
     * Finds one product with "pending prices" flag set to true.
     */
    public function findOneByPendingPrices(string $type): ?ProductInterface;

    /**
     * Finds one duplicate by reference.
     *
     * @param array<ProductInterface> $ignore
     */
    public function findDuplicateByReference(
        ProductInterface $product,
        array            $ignore = []
    ): ?ProductInterface;

    /**
     * Finds a duplicate by designation and reference.
     *
     * @param array<ProductInterface> $ignore
     */
    public function findDuplicateByDesignationAndBrand(
        ProductInterface $product,
        array            $ignore = []
    ): ?ProductInterface;

    /**
     * Finds by sku or references.
     *
     * @return array<ProductInterface>
     */
    public function findBySkuOrReferences(string $code): array;

    /**
     * Returns products that should be added to sitemap.
     *
     * @return array<ProductInterface>
     */
    public function findForSitemap(): array;

    /**
     * Loads the product's medias.
     */
    public function loadMedias(ProductInterface $product): void;

    /**
     * Loads the product's option groups and options.
     */
    public function loadOptions(ProductInterface $product): void;

    /**
     * Loads the variable's variants.
     */
    public function loadVariants(ProductInterface $variable): void;

    /**
     * Loads the bundles slots into the bundle product.
     */
    public function loadBundleSlots(ProductInterface $bundle): void;

    /**
     * Loads the bundles slots into the configurable product.
     */
    public function loadConfigurableSlots(ProductInterface $configurable): void;

    /**
     * Returns the product for which stats should be updated.
     */
    public function findNextStatUpdate(DateTimeInterface $maxDate = null): ?ProductInterface;

    /**
     * Returns the visible products with bestseller mode set to 'always'.
     *
     * @param array $options The options : limit, exclude (ids), id_only
     *
     * @return array<ProductInterface>
     */
    public function findBestSellers(array $options = []): array;

    /**
     * Returns the visible products with cross-selling mode set to 'always'.
     *
     * @param array $options The options : limit, exclude (ids), id_only
     *
     * @return array<ProductInterface>
     */
    public function findCrossSelling(array $options = []): array;

    /**
     * @return array<ProductInterface>
     */
    public function findForHighlight(): array;

    /**
     * @return array<ProductInterface>
     */
    public function findForExport(ExportConfig $config): array;

    /**
     * Joins prices for sorting.
     *
     * Adds 'sellPrice' select to the given query builder.
     * Both 'customer_group' and 'invoice_country' must be set.
     *
     * @param QueryBuilder $qb    The product query builder.
     * @param string|null  $alias The product alias.
     *
     * @return $this|ProductRepositoryInterface
     */
    public function joinPrice(QueryBuilder $qb, string $alias = null): ProductRepositoryInterface;
}
