<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

use function in_array;

/**
 * Class AbstractInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvalidator
{
    protected ProductRepositoryInterface $productRepository;
    private string                       $entityClass;
    private string                       $flagProperty;

    /** @var array<int> */
    private array $productIds;
    /** @var array<int> */
    private array $brandIds;
    /** @var array<int> */
    private array $groupIds;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        string                     $entityClass,
        string                     $flagProperty
    ) {
        $this->productRepository = $productRepository;
        $this->entityClass = $entityClass;
        $this->flagProperty = $flagProperty;

        $this->clear();
    }

    /**
     * Clears the invalidation source's ids.
     */
    public function clear(): void
    {
        $this->productIds = [];
        $this->brandIds = [];
        $this->groupIds = [];
    }

    /**
     * Invalidates scheduled entities.
     */
    public function flush(EntityManagerInterface $manager): void
    {
        if (!empty($this->productIds)) {
            $qb = $manager->createQueryBuilder();
            $qb
                ->update($this->productRepository->getClassName(), 'p')
                ->set('p.' . $this->flagProperty, ':flag')
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
                ->set('p.' . $this->flagProperty, ':flag')
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
                ->from($this->entityClass, 'object')
                ->select('object')
                ->andWhere($qb->expr()->in('object.group', ':group_ids'))
                ->andWhere($qb->expr()->eq('object.product', 'p.id'))
                ->getDQL();

            $qb = $manager->createQueryBuilder();
            $qb
                ->update($this->productRepository->getClassName(), 'p')
                ->set('p.' . $this->flagProperty, ':flag')
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
    public function invalidateParents(Model\ProductInterface $product): void
    {
        if (Model\ProductTypes::isConfigurableType($product)) {
            return;
        }

        if (Model\ProductTypes::isVariantType($product)) {
            $this->invalidateByProductId($product->getParent()->getId());
        }

        $parents = $this->productRepository->findParentsByBundled($product, false, true);
        foreach ($parents as $id) {
            $this->invalidateByProductId($id);
        }

        $parents = $this->productRepository->findParentsByOptionProduct($product, false, true);
        foreach ($parents as $id) {
            $this->invalidateByProductId($id);
        }

        $parents = $this->productRepository->findParentsByComponent($product, true);
        foreach ($parents as $id) {
            $this->invalidateByProductId($id);
        }
    }

    /**
     * Schedule offer invalidation by product.
     */
    public function invalidateByProduct(Model\ProductInterface $product): void
    {
        $this->invalidateByProductId($product->getId());
    }

    /**
     * Schedule offer invalidation by product id.
     */
    public function invalidateByProductId(?int $id): void
    {
        $this->invalidateId($id, $this->productIds);
    }

    /**
     * Schedule offer invalidation by brand.
     */
    public function invalidateByBrand(Model\BrandInterface $brand): void
    {
        $this->invalidateByBrandId($brand->getId());
    }

    /**
     * Schedule offer invalidation by brand id.
     */
    public function invalidateByBrandId(?int $id): void
    {
        $this->invalidateId($id, $this->brandIds);
    }

    /**
     * Schedule offer invalidation by customer group.
     */
    public function invalidateByCustomerGroup(CustomerGroupInterface $group): void
    {
        $this->invalidateByCustomerGroupId($group->getId());
    }

    /**
     * Schedule offer invalidation by customer group id.
     */
    public function invalidateByCustomerGroupId(?int $id): void
    {
        $this->invalidateId($id, $this->groupIds);
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

    private function invalidateId(?int $id, array &$list): void
    {
        if (null === $id) {
            return;
        }

        if (in_array($id, $list, true)) {
            return;
        }

        $list[] = $id;
    }
}
