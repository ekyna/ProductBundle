<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DataFixtures;

use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;

/**
 * Class FixturesListener
 * @package Ekyna\Bundle\ProductBundle\DataFixtures
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FixturesListener
{
    public function __construct(
        private readonly OfferInvalidator $offerInvalidator,
        private readonly PriceInvalidator $priceInvalidator,
    ) {
    }

    public function onStart(): void
    {
        $this->offerInvalidator->toggleMessages(false);
        $this->priceInvalidator->toggleMessages(false);
    }

    public function onEnd(): void
    {
        $this->offerInvalidator->toggleMessages(true);
        $this->priceInvalidator->toggleMessages(true);
    }
}
