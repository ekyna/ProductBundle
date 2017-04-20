<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PricingRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingRuleInterface extends ResourceInterface
{
    public function getPricing(): ?PricingInterface;

    public function setPricing(?PricingInterface $pricing): PricingRuleInterface;

    public function getMinQuantity(): Decimal;

    public function setMinQuantity(Decimal $quantity): PricingRuleInterface;

    public function getPercent(): Decimal;

    public function setPercent(Decimal $percent): PricingRuleInterface;
}
