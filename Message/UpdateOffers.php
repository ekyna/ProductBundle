<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Message;

/**
 * Class UpdateOffers
 * @package Ekyna\Bundle\ProductBundle\Message
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateOffers
{
    public function __construct(private readonly int $productId)
    {
    }

    public function getProductId(): int
    {
        return $this->productId;
    }
}
