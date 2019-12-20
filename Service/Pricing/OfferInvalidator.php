<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;

/**
 * Class OfferInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferInvalidator
{
    /**
     * @var string
     */
    private $productClass;

    /**
     * @var int[]
     */
    private $productIds;

    /**
     * @var int[]
     */
    private $brandIds;


    /**
     * Constructor.
     *
     * @param string $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;

        $this->clear();
    }

    /**
     * Clears the products and brands ids.
     */
    public function clear(): void
    {
        $this->productIds = [];
        $this->brandIds = [];
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
                ->update($this->productClass, 'p')
                ->set('p.pendingOffers', ':flag')
                ->andWhere($qb->expr()->in('p.id', ':product_ids'))
                ->getQuery()
                ->useQueryCache(false)
                ->useResultCache(false)
                ->setParameters([
                    'flag'        => 1,
                    'product_ids' => $this->productIds,
                ])
                ->execute();
        }

        if (!empty($this->brandIds)) {
            $qb = $manager->createQueryBuilder();
            $qb
                ->update($this->productClass, 'p')
                ->set('p.pendingOffers', ':flag')
                ->andWhere($qb->expr()->in('p.type', ':types'))
                ->andWhere($qb->expr()->in('IDENTITY(p.brand)', ':brand_ids'))
                ->getQuery()
                ->useQueryCache(false)
                ->useResultCache(false)
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

        $this->clear();
    }

    /**
     * Invalidates offers for the given pricing.
     *
     * @param PricingInterface $pricing
     */
    public function invalidatePricing(PricingInterface $pricing): void
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
     *
     * @param SpecialOfferInterface $specialOffer
     */
    public function invalidateSpecialOffer(SpecialOfferInterface $specialOffer): void
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

    /**
     * Schedule offer invalidation by brand id.
     *
     * @param int $id
     */
    public function invalidateByBrandId(int $id = null): void
    {
        if ($id && !in_array($id, $this->brandIds)) {
            $this->brandIds[] = $id;
        }
    }
}
