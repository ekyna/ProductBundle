<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface PriceRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PriceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds prices by product.
     *
     * @param ProductInterface $product
     * @param bool             $asArray
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\Price[]|array[]
     */
    public function findByProduct(ProductInterface $product, $asArray = false);

    /**
     * Finds one price by product and context.
     *
     * @param ProductInterface $product
     * @param ContextInterface $context
     * @param bool             $useCache
     *
     * @return array|null
     */
    public function findOneByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        $useCache = true
    );
}
