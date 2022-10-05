<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Message;

/**
 * Class ProductDeletion
 * @package Ekyna\Bundle\ProductBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductDeletion
{
    public function __construct(private readonly int $productId)
    {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
