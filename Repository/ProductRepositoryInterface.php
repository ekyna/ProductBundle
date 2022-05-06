<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;
use Ekyna\Component\Resource\Repository\TranslatableRepositoryInterface;

/**
 * Interface ProductRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\ProductInterface find($id)
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
    public function findOneById(int $id): ?Model\ProductInterface;

    /**
     * Finds the product by slug (if product and its brand and categories are visible).
     */
    public function findOneBySlug(string $slug): ?Model\ProductInterface;

    /**
     * Finds the product by reference (if product and its brand and categories are visible).
     */
    public function findOneByReference(string $reference): ?Model\ProductInterface;

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
    ): ?Model\ProductInterface;

    /**
     * Finds products by brand.
     *
     * @return array<Model\ProductInterface>
     */
    public function findByBrand(Model\BrandInterface $brand): array;

    /**
     * Finds products by category, optionally including children categories.
     *
     * @return array<Model\ProductInterface>
     */
    public function findByCategory(Model\CategoryInterface $category, bool $recursive = false): array;

    /**
     * Finds the parents products of the given bundled product.
     *
     * @return array<Model\ProductInterface>
     */
    public function findParentsByBundled(
        Model\ProductInterface $bundled,
        bool                   $requiredSlots = false,
        bool                   $idOnly = false
    ): array;

    /**
     * Finds the products having the given product as option.
     *
     * @return array<Model\ProductInterface>
     */
    public function findParentsByOptionProduct(
        Model\ProductInterface $product,
        bool                   $requiredGroups = false,
        bool                   $idOnly = false
    ): array;

    /**
     * Finds the products having the given product as component.
     *
     * @return array<Model\ProductInterface>
     */
    public function findParentsByComponent(Model\ProductInterface $product, bool $idOnly = false): array;

    /**
     * Finds the "out of stock" products for the given mode.
     *
     * @return array<Model\ProductInterface>
     */
    public function findOutOfStockProducts(string $mode): array;

    /**
     * Finds the products for inventory export.
     *
     * @return array<Model\ProductInterface>
     */
    public function findForInventoryExport(): array;

    /**
     * Finds one product with "pending offers" flag set to true.
     */
    public function findOneByPendingOffers(string $type): ?Model\ProductInterface;

    /**
     * Finds one product with "pending prices" flag set to true.
     */
    public function findOneByPendingPrices(string $type): ?Model\ProductInterface;

    /**
     * Finds one duplicate by reference.
     *
     * @param array<Model\ProductInterface> $ignore
     */
    public function findDuplicateByReference(
        Model\ProductInterface $product,
        array                  $ignore = []
    ): ?Model\ProductInterface;

    /**
     * Finds a duplicate by designation and reference.
     *
     * @param array<Model\ProductInterface> $ignore
     */
    public function findDuplicateByDesignationAndBrand(
        Model\ProductInterface $product,
        array                  $ignore = []
    ): ?Model\ProductInterface;

    /**
     * Finds by sku or references.
     *
     * @return array<Model\ProductInterface>
     */
    public function findBySkuOrReferences(string $code): array;

    /**
     * Returns products that should be added to sitemap.
     *
     * @return array<Model\ProductInterface>
     */
    public function findForSitemap(): array;

    /**
     * Loads the product's medias.
     */
    public function loadMedias(Model\ProductInterface $product): void;

    /**
     * Loads the product's option groups and options.
     */
    public function loadOptions(Model\ProductInterface $product): void;

    /**
     * Loads the variable's variants.
     */
    public function loadVariants(Model\ProductInterface $variable): void;

    /**
     * Loads the bundles slots into the bundle product.
     */
    public function loadBundleSlots(Model\ProductInterface $bundle): void;

    /**
     * Loads the bundles slots into the configurable product.
     */
    public function loadConfigurableSlots(Model\ProductInterface $configurable): void;

    /**
     * Returns the product for which stats should be updated.
     */
    public function findNextStatUpdate(DateTimeInterface $maxDate = null): ?Model\ProductInterface;

    /**
     * Returns the visible products with best-seller mode set to 'always'.
     *
     * @param array $options The options : limit, exclude (ids), id_only
     *
     * @return array<Model\ProductInterface>
     */
    public function findBestSellers(array $options = []): array;

    /**
     * Returns the visible products with cross-selling mode set to 'always'.
     *
     * @param array $options The options : limit, exclude (ids), id_only
     *
     * @return array<Model\ProductInterface>
     */
    public function findCrossSelling(array $options = []): array;

    /**
     * @return array<Model\ProductInterface>
     */
    public function findForHighlight(): array;

    /**
     * @return array<Model\ProductInterface>
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
