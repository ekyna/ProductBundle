<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface OfferRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OfferRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Find offers by product, context and quantity.
     *
     * @param ProductInterface $product
     * @param ContextInterface $context
     * @param bool             $useCache
     *
     * @return array [['min_qty' => (float), 'percent' => (float), 'price' => (float)]]
     */
    public function findByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        bool $useCache = true
    ): array;

    /**
     * Find one offer for the given product, context and quantity.
     *
     * @param ProductInterface $product
     * @param ContextInterface $context
     * @param float            $quantity
     * @param bool             $useCache
     *
     * @return null|array ['percent' => (float), 'special_offer_id' => (int), 'pricing_id' => (int)]
     */
    public function findOneByProductAndContextAndQuantity(
        ProductInterface $product,
        ContextInterface $context,
        float $quantity = 1.0,
        bool $useCache = true
    ): ?array;

    /**
     * Finds offers by product.
     *
     * @param ProductInterface $product
     * @param bool             $asArray
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\Offer[]|array[]
     */
    public function findByProduct(ProductInterface $product, bool $asArray = false): array;
}
