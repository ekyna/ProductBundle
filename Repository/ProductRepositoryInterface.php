<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface ProductRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductRepositoryInterface extends TranslatableResourceRepositoryInterface
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
     * Finds the parents products of the given bundled product.
     *
     * @param Model\ProductInterface $bundled
     *
     * @return array|Model\ProductInterface[]
     */
    public function findParentsByBundled(Model\ProductInterface $bundled);

    /**
     * Finds products by category, optionally including children categories.
     *
     * @param Model\CategoryInterface $category
     * @param bool              $recursive
     *
     * @return array|Model\ProductInterface[]
     */
    public function findByCategory(Model\CategoryInterface $category, $recursive = false);

    /**
     * Finds products by brand.
     *
     * @param Model\BrandInterface $brand
     *
     * @return array|Model\ProductInterface[]
     */
    public function findByBrand(Model\BrandInterface $brand);
}
