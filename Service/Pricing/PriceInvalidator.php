<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;

/**
 * Class PriceInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceInvalidator
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var int[]
     */
    private $productIds;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;

        $this->clear();
    }

    /**
     * Clears the products and brands ids.
     */
    public function clear(): void
    {
        $this->productIds = [];
    }

    /**
     * Invalidates scheduled offers.
     *
     * @param EntityManagerInterface $manager
     */
    public function flush(EntityManagerInterface $manager): void
    {
        if (!empty($this->productIds)) {
            $qb = $manager->createQueryBuilder();
            $qb
                ->update($this->productRepository->getClassName(), 'p')
                ->set('p.pendingPrices', ':flag')
                ->andWhere($qb->expr()->in('p.id', ':product_ids'))
                ->getQuery()
                ->useQueryCache(false)
                ->disableResultCache()
                ->setParameters([
                    'flag'        => 1,
                    'product_ids' => $this->productIds,
                ])
                ->execute();
        }

        $this->clear();
    }

    /**
     * Invalidates product's parents prices (by bundle choice or option product).
     *
     * @param Model\ProductInterface $product
     */
    public function invalidateParentsPrices(Model\ProductInterface $product): void
    {
        if (Model\ProductTypes::isConfigurableType($product)) {
            return;
        }

        if (Model\ProductTypes::isVariantType($product)) {
            $this->invalidateByProductId($product->getParent()->getId());
        }

        $parents = $this->productRepository->findParentsByBundled($product);
        foreach ($parents as $parent) {
            $this->invalidateByProductId($parent->getId());
        }

        $parents = $this->productRepository->findParentsByOptionProduct($product);
        foreach ($parents as $parent) {
            $this->invalidateByProductId($parent->getId());
        }

        $parents = $this->productRepository->findParentsByComponent($product);
        foreach ($parents as $parent) {
            $this->invalidateByProductId($parent->getId());
        }
    }

    /**
     * Schedule offer invalidation by product.
     *
     * @param Model\ProductInterface $product
     */
    public function invalidateByProduct(Model\ProductInterface $product): void
    {
        if (null === $id = $product->getId()) {
            return;
        }

        $this->invalidateByProductId($id);
    }

    /**
     * Schedule offer invalidation by product id.
     *
     * @param int $id
     */
    public function invalidateByProductId(int $id = null): void
    {
        if ($id && !in_array($id, $this->productIds)) {
            $this->productIds[] = $id;
        }
    }
}
