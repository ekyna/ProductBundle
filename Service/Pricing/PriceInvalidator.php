<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;

/**
 * Class PriceInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceInvalidator extends AbstractInvalidator
{
    public function __construct(ProductRepositoryInterface $productRepository, string $offerClass)
    {
        parent::__construct($productRepository, $offerClass, 'pendingPrices');
    }
}
