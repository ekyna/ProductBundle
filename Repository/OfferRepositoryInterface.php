<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Entity\Offer;
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
     * @param ContextInterface       $context
     * @param bool                   $useCache
     *
     * @return array
     */
    public function findByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        $useCache = true
    );

    /**
     * Find offers by product, context and quantity.
     *
     * @param ProductInterface $product
     * @param ContextInterface       $context
     * @param float                  $quantity
     * @param bool                   $useCache
     *
     * @return array
     */
    public function findOneByProductAndContextAndQuantity(
        ProductInterface $product,
        ContextInterface $context,
        $quantity = 1.0,
        $useCache = true
    );

    /**
     * Finds offers by product.
     *
     * @param ProductInterface $product
     * @param bool                   $asArray
     *
     * @return Offer[]|array[]
     */
    public function findByProduct(ProductInterface $product, $asArray = false);
}