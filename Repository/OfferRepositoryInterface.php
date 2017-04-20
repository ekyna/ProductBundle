<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

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
     * @return array [['min_qty' => (Decimal), 'percent' => (string), 'price' => (Decimal)]]
     */
    public function findByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        bool             $useCache = true
    ): array;

    /**
     * Find one offer for the given product, context and quantity.
     *
     * @return null|array ['percent' => (string), 'special_offer_id' => (int), 'pricing_id' => (int)]
     */
    public function findOneByProductAndContextAndQuantity(
        ProductInterface $product,
        ContextInterface $context,
        Decimal          $quantity = null,
        bool             $useCache = true
    ): ?array;

    /**
     * Finds offers by product.
     *
     * @return array<Offer>|array<array>
     */
    public function findByProduct(ProductInterface $product, bool $asArray = false): array;
}
