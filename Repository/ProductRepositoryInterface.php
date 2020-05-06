<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface ProductRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\ProductInterface|null find($id)
 */
interface ProductRepositoryInterface extends TranslatableResourceRepositoryInterface, SubjectRepositoryInterface
{
    /**
     * Returns the product update date by it's id.
     *
     * @param int   $id
     * @param bool  $visible
     * @param array $types
     *
     * @return \DateTime|null
     */
    public function getUpdateDateById($id, $visible = true, array $types = null);

    /**
     * Returns the product update date by it's slug.
     *
     * @param string $slug
     * @param bool   $visible
     * @param array  $types
     *
     * @return \DateTime|null
     */
    public function getUpdateDateBySlug($slug, $visible = true, array $types = null);

    /**
     * Finds the product by id (if product is visible, as well as its brand and categories).
     *
     * @param int $id
     *
     * @return Model\ProductInterface|null
     */
    public function findOneById($id);

    /**
     * Finds the product by slug (if product is visible, as well as its brand and categories).
     *
     * @param string $slug
     *
     * @return Model\ProductInterface|null
     */
    public function findOneBySlug($slug);

    /**
     * Finds the product by reference (if product is visible, as well as its brand and categories).
     *
     * @param string $reference
     *
     * @return Model\ProductInterface|null
     */
    public function findOneByReference($reference);

    /**
     * Finds one product by external reference.
     *
     * @param string   $code    The product reference code
     * @param string[] $types   To filter references types
     * @param bool     $visible Whether to fetch visible products only
     *
     * @return Model\ProductInterface|null
     */
    public function findOneByExternalReference(string $code, array $types = [], bool $visible = true);

    /**
     * Finds products by brand.
     *
     * @param Model\BrandInterface $brand
     *
     * @return array|Model\ProductInterface[]
     */
    public function findByBrand(Model\BrandInterface $brand);

    /**
     * Finds products by category, optionally including children categories.
     *
     * @param Model\CategoryInterface $category
     * @param bool                    $recursive
     *
     * @return array|Model\ProductInterface[]
     */
    public function findByCategory(Model\CategoryInterface $category, $recursive = false);

    /**
     * Finds the parents products of the given bundled product.
     *
     * @param Model\ProductInterface $bundled
     * @param bool                   $requiredSlots
     *
     * @return array|Model\ProductInterface[]
     */
    public function findParentsByBundled(Model\ProductInterface $bundled, $requiredSlots = false);

    /**
     * Finds the products having the given product as option.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $requiredGroups
     *
     * @return array|Model\ProductInterface[]
     */
    public function findParentsByOptionProduct(Model\ProductInterface $product, $requiredGroups = false);

    /**
     * Finds the products having the given product as component.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    public function findParentsByComponent(Model\ProductInterface $product);

    /**
     * Finds the "out of stock" products for the given mode.
     *
     * @param string $mode
     *
     * @return array|Model\ProductInterface[]
     */
    public function findOutOfStockProducts($mode);

    /**
     * Finds the products for inventory export.
     *
     * @return array|Model\ProductInterface[]
     */
    public function findForInventoryExport();

    /**
     * Finds a product with "pending offers" flag set to true.
     *
     * @param string $type
     *
     * @return Model\ProductInterface
     */
    public function findOneByPendingOffers(string $type);

    /**
     * Finds a product with "pending prices" flag set to true.
     *
     * @param string $type
     *
     * @return Model\ProductInterface
     */
    public function findOneByPendingPrices(string $type);

    /**
     * Finds a duplicate by reference.
     *
     * @param Model\ProductInterface   $product
     * @param Model\ProductInterface[] $ignore
     *
     * @return Model\ProductInterface|null
     */
    public function findDuplicateByReference(
        Model\ProductInterface $product,
        array $ignore = []
    ): ?Model\ProductInterface;

    /**
     * Finds a duplicate by designation and reference.
     *
     * @param Model\ProductInterface   $product
     * @param Model\ProductInterface[] $ignore
     *
     * @return Model\ProductInterface|null
     */
    public function findDuplicateByDesignationAndBrand(
        Model\ProductInterface $product,
        array $ignore = []
    ): ?Model\ProductInterface;

    /**
     * Finds by sku or references.
     *
     * @param string $code
     *
     * @return Model\ProductInterface[]
     */
    public function findBySkuOrReferences(string $code): array;

    /**
     * Returns products that should be added to sitemap.
     *
     * @return Model\ProductInterface[]
     */
    public function findForSitemap(): array;

    /**
     * Loads the product's medias.
     *
     * @param Model\ProductInterface $product
     */
    public function loadMedias(Model\ProductInterface $product);

    /**
     * Loads the product's option groups and options.
     *
     * @param Model\ProductInterface $product
     */
    public function loadOptions(Model\ProductInterface $product);

    /**
     * Loads the variable's variants.
     *
     * @param Model\ProductInterface $variable
     */
    public function loadVariants(Model\ProductInterface $variable);

    /**
     * Loads the bundles slots into the bundle product.
     *
     * @param Model\ProductInterface $bundle
     */
    public function loadBundleSlots(Model\ProductInterface $bundle);

    /**
     * Loads the bundles slots into the configurable product.
     *
     * @param Model\ProductInterface $configurable
     */
    public function loadConfigurableSlots(Model\ProductInterface $configurable);

    /**
     * Returns the product for which stats should be updated.
     *
     * @param \DateTime $maxDate
     *
     * @return Model\ProductInterface|null
     */
    public function findNextStatUpdate(\DateTime $maxDate = null);

    /**
     * Returns the visible products with best seller mode set to 'always'.
     *
     * @param array $options The options : limit, exclude (ids), id_only
     *
     * @return Model\ProductInterface[]
     */
    public function findBestSellers(array $options = []): array;

    /**
     * Returns the visible products with cross selling mode set to 'always'.
     *
     * @param array $options The options : limit, exclude (ids), id_only
     *
     * @return Model\ProductInterface[]
     */
    public function findCrossSelling(array $options = []): array;

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
