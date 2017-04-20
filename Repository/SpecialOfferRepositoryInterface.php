<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface SpecialOfferRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SpecialOfferRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds special offers rules by product.
     */
    public function findRulesByProduct(ProductInterface $product): array;

    /**
     * Returns special offers starting today or ending yesterday.
     *
     * @return array<SpecialOfferInterface>
     */
    public function findStartingTodayOrEndingYesterday(): array;
}
