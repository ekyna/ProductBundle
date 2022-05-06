<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;

/**
 * Class OfferInvalidator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferInvalidator extends AbstractInvalidator
{
    public function __construct(ProductRepositoryInterface $productRepository, string $offerClass)
    {
        parent::__construct($productRepository, $offerClass, 'pendingOffers');
    }
}
