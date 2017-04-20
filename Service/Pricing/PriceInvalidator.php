<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class PriceInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceInvalidator
{
    protected ProductRepositoryInterface $productRepository;
    private string                       $priceClass;
    /** @var array<int> */
    private array $productIds;
    /** @var array<int> */
    private array $brandIds;
    /** @var array<int> */
    private array $groupIds;

    public function __construct(ProductRepositoryInterface $productRepository, string $priceClass)
    {
        $this->productRepository = $productRepository;
        $this->priceClass = $priceClass;

        $this->clear();
    }

    /**
     * Clears the products and brands ids.
     */
    public function clear(): void
    {
        $this->productIds = [];
        $this->brandIds = [];
        $this->groupIds = [];
    }

    /**
     * Invalidates scheduled offers.
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

        if (!empty($this->brandIds)) {
            $qb = $manager->createQueryBuilder();
            $qb
                ->update($this->productRepository->getClassName(), 'p')
                ->set('p.pendingPrices', ':flag')
                ->andWhere($qb->expr()->in('p.type', ':types'))
                ->andWhere($qb->expr()->in('IDENTITY(p.brand)', ':brand_ids'))
                ->getQuery()
                ->useQueryCache(false)
                ->disableResultCache()
                ->setParameters([
                    'flag'      => 1,
                    'brand_ids' => $this->brandIds,
                    'types'     => [
                        ProductTypes::TYPE_SIMPLE,
                        ProductTypes::TYPE_VARIANT,
                    ],
                ])
                ->execute();
        }

        if (!empty($this->groupIds)) {
            $qb = $manager->createQueryBuilder();
            $subQuery = $qb
                ->from($this->priceClass, 'price')
                ->select('price')
                ->where($qb->expr()->in('price.group', ':group_ids'))
                ->where($qb->expr()->eq('price.product', 'p.id'))
                ->getDQL();

            $qb = $manager->createQueryBuilder();
            $qb
                ->update($this->productRepository->getClassName(), 'p')
                ->set('p.pendingPrices', ':flag')
                ->andWhere($qb->expr()->exists($subQuery))
                ->andWhere($qb->expr()->in('p.type', ':types'))
                ->getQuery()
                ->useQueryCache(false)
                ->disableResultCache()
                ->setParameters([
                    'flag'      => 1,
                    'group_ids' => $this->groupIds,
                    'types'     => [
                        ProductTypes::TYPE_SIMPLE,
                        ProductTypes::TYPE_VARIANT,
                    ],
                ])
                ->execute();
        }

        $this->clear();
    }

    /**
     * Invalidates product's parents prices (by bundle choice or option product).
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
     */
    public function invalidateByProductId(int $id = null): void
    {
        if ($id && !in_array($id, $this->productIds, true)) {
            $this->productIds[] = $id;
        }
    }

    /**
     * Schedule offer invalidation by brand.
     */
    public function invalidateByBrand(Model\BrandInterface $brand): void
    {
        if (null === $id = $brand->getId()) {
            return;
        }

        $this->invalidateByBrandId($id);
    }

    /**
     * Schedule offer invalidation by brand id.
     */
    public function invalidateByBrandId(int $id = null): void
    {
        if ($id && !in_array($id, $this->brandIds, true)) {
            $this->brandIds[] = $id;
        }
    }

    /**
     * Schedule offer invalidation by customer group.
     */
    public function invalidateByCustomerGroup(CustomerGroupInterface $group): void
    {
        if (null === $id = $group->getId()) {
            return;
        }

        $this->invalidateByCustomerGroupId($id);
    }

    /**
     * Schedule offer invalidation by customer group id.
     */
    public function invalidateByCustomerGroupId(int $id = null): void
    {
        if ($id && !in_array($id, $this->groupIds, true)) {
            $this->groupIds[] = $id;
        }
    }

    /**
     * Invalidates offers for the given pricing.
     */
    public function invalidatePricing(Model\PricingInterface $pricing): void
    {
        if (null !== $product = $pricing->getProduct()) {
            $this->invalidateByProductId($product->getId());

            return;
        }

        foreach ($pricing->getBrands() as $brand) {
            $this->invalidateByBrandId($brand->getId());
        }
    }

    /**
     * Invalidates offers for the given special offer.
     */
    public function invalidateSpecialOffer(Model\SpecialOfferInterface $specialOffer): void
    {
        if (null !== $product = $specialOffer->getProduct()) {
            $this->invalidateByProductId($product->getId());

            return;
        }

        foreach ($specialOffer->getProducts() as $product) {
            $this->invalidateByProductId($product->getId());
        }

        foreach ($specialOffer->getBrands() as $brand) {
            $this->invalidateByBrandId($brand->getId());
        }
    }
}
