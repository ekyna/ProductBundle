<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Message;

/**
 * Class UpdateProductStat
 * @package Ekyna\Bundle\ProductBundle\Message
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UpdateProductStat
{
    public function __construct(
        private readonly int  $productId,
        private readonly bool $force,
    ) {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function isForce(): bool
    {
        return $this->force;
    }
}
