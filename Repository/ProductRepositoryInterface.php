<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Subject\Repository\SubjectRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface ProductRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductRepositoryInterface extends TranslatableResourceRepositoryInterface, SubjectRepositoryInterface
{
    /**
     * Finds the product by id.
     *
     * @param int $id
     *
     * @return Model\ProductInterface|null
     */
    public function findOneById($id);

    /**
     * Finds the product by slug.
     *
     * @param string $slug
     *
     * @return Model\ProductInterface|null
     */
    public function findOneBySlug($slug);

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
     * Finds the "out of stock" products for the given mode.
     *
     * @param string $mode
     *
     * @return array|Model\ProductInterface[]
     */
    public function findOutOfStockProducts($mode);

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
}
