<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Hydrator\IdHydrator;
use Ekyna\Component\Resource\Message\MessageQueue;

use function array_push;
use function array_unique;
use function in_array;

/**
 * Class AbstractInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvalidator
{
    /** @var array<int> */
    private array $productIds;
    /** @var array<int> */
    private array $brandIds;
    /** @var array<int> */
    private array $customerGroupIds;
    /** @var array<int> */
    private array $pricingGroupIds;

    private bool $messagesEnabled = true;

    public function __construct(
        private readonly EntityManagerInterface     $entityManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly MessageQueue               $messageQueue,
        private readonly string                     $entityClass,
        private readonly string                     $flagProperty
    ) {
        $this->clear();
    }

    public function toggleMessages(bool $enabled): void
    {
        $this->messagesEnabled = $enabled;
    }

    /**
     * Clears the invalidation source's ids.
     */
    public function clear(): void
    {
        $this->productIds = [];
        $this->brandIds = [];
        $this->customerGroupIds = [];
        $this->pricingGroupIds = [];
    }

    /**
     * Invalidates scheduled entities.
     */
    public function flush(): void
    {
        $ex = new Expr();

        $clauses = $parameters = [];

        if (!empty($this->pricingGroupIds)) {
            $clauses[] = $ex->in('IDENTITY(p.pricingGroup)', ':pricing_group_ids');
            $parameters['pricing_group_ids'] = $this->pricingGroupIds;
        }

        if (!empty($this->brandIds)) {
            $clauses[] = $ex->in('IDENTITY(p.brand)', ':brand_ids');
            $parameters['brand_ids'] = $this->brandIds;
        }

        if (!empty($this->customerGroupIds)) {
            $subQb = $this->entityManager->createQueryBuilder();
            $subQuery = $subQb
                ->from($this->entityClass, 'object')
                ->select('object')
                ->andWhere($ex->in('object.group', ':customer_group_ids'))
                ->andWhere($ex->eq('object.product', 'p.id'))
                ->getDQL();

            $clauses[] = $ex->exists($subQuery);
            $parameters['customer_group_ids'] = $this->customerGroupIds;
        }

        if (!empty($clauses)) {
            $clauses = [
                $ex->andX(
                    $ex->in('p.type', ':types'),
                    $ex->orX(...$clauses)
                ),
            ];
            $parameters['types'] = [
                ProductTypes::TYPE_SIMPLE,
                ProductTypes::TYPE_VARIANT,
            ];
        }

        $productIds = $this->productIds;

        if (empty($clauses) && empty($productIds)) {
            return;
        }

        $this->clear();

        $this->updateFlag($clauses, $parameters, $productIds);

        $this->sendMessages($clauses, $parameters, $productIds);
    }

    private function updateFlag(array $clauses, array $parameters, array $productIds): void
    {
        $ex = new Expr();

        if (!empty($clauses)) {
            if (!empty($productIds)) {
                $clauses[] = $ex->in('p.id', ':product_ids');
                $parameters['product_ids'] = $productIds;
            }
            $clauses = $ex->orX(...$clauses);
        } elseif (!empty($productIds)) {
            $clauses = $ex->in('p.id', ':product_ids');
            $parameters['product_ids'] = $productIds;
        } else {
            return;
        }

        $parameters['flag'] = 1;

        // Update products flags
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->update($this->productRepository->getClassName(), 'p')
            ->set('p.' . $this->flagProperty, ':flag')
            ->andWhere(
                $clauses,
                $ex->neq('p.' . $this->flagProperty, ':flag')
            )
            ->getQuery()
            ->useQueryCache(false)
            ->disableResultCache()
            ->setParameters($parameters)
            ->execute();
    }

    private function sendMessages(array $clauses, array $parameters, array $productIds): void
    {
        if (!$this->messagesEnabled) {
            return;
        }

        if (!empty($clauses)) {
            $qb = $this->entityManager->createQueryBuilder();
            $result = $qb
                ->from($this->productRepository->getClassName(), 'p')
                ->select(['p.id'])
                ->orWhere(...$clauses)
                ->getQuery()
                ->useQueryCache(false)
                ->disableResultCache()
                ->setParameters($parameters)
                ->getResult(IdHydrator::NAME);

            if (empty($productIds)) {
                $productIds = $result;
            } else {
                array_push($productIds, ...$result);
                $productIds = array_unique($productIds);
            }
        }

        if (empty($productIds)) {
            return;
        }

        // Send update messages
        foreach ($productIds as $productId) {
            $this->messageQueue->addMessage($this->createMessage($productId));
        }
    }

    abstract protected function createMessage(int $productId): object;

    /**
     * Invalidates product's parents (by bundle choice or option product).
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
     * Schedule invalidation by product.
     */
    public function invalidateByProduct(Model\ProductInterface $product): void
    {
        if (null !== $id = $product->getId()) {
            $this->invalidateByProductId($id);

            return;
        }

        if (!$this->messagesEnabled) {
            return;
        }

        $this->messageQueue->addMessage(function () use ($product) {
            return $this->createMessage($product->getId());
        });
    }

    /**
     * Schedule invalidation by product id.
     */
    public function invalidateByProductId(?int $id): void
    {
        $this->invalidateId($id, $this->productIds);
    }

    /**
     * Schedule invalidation by brand.
     */
    public function invalidateByBrand(Model\BrandInterface $brand): void
    {
        $this->invalidateByBrandId($brand->getId());
    }

    /**
     * Schedule invalidation by brand id.
     */
    public function invalidateByBrandId(?int $id): void
    {
        $this->invalidateId($id, $this->brandIds);
    }

    /**
     * Schedule invalidation by pricing group.
     */
    public function invalidateByPricingGroup(Model\PricingGroupInterface $group): void
    {
        $this->invalidateByPricingGroupId($group->getId());
    }

    /**
     * Schedule invalidation by pricing group id.
     */
    public function invalidateByPricingGroupId(?int $id): void
    {
        $this->invalidateId($id, $this->pricingGroupIds);
    }

    /**
     * Schedule invalidation by customer group.
     */
    public function invalidateByCustomerGroup(CustomerGroupInterface $group): void
    {
        $this->invalidateByCustomerGroupId($group->getId());
    }

    /**
     * Schedule invalidation by customer group id.
     */
    public function invalidateByCustomerGroupId(?int $id): void
    {
        $this->invalidateId($id, $this->customerGroupIds);
    }

    /**
     * Invalidates for the given pricing.
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

        foreach ($pricing->getPricingGroups() as $group) {
            $this->invalidateByPricingGroupId($group->getId());
        }
    }

    /**
     * Invalidates for the given special offer.
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

        foreach ($specialOffer->getPricingGroups() as $group) {
            $this->invalidateByPricingGroupId($group->getId());
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
