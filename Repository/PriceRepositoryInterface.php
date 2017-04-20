<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

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
     * @return array<Price>|array<array>
     */
    public function findByProduct(ProductInterface $product, bool $asArray = false): array;

    /**
     * Finds one price by product and context.
     */
    public function findOneByProductAndContext(
        ProductInterface $product,
        ContextInterface $context,
        bool $useCache = true
    ): ?array;
}
