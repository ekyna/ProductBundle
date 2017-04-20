<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class PricingRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRule implements Model\PricingRuleInterface
{
    protected ?int                    $id      = null;
    protected ?Model\PricingInterface $pricing = null;
    protected Decimal                 $minQuantity;
    protected Decimal                 $percent;

    public function __construct()
    {
        $this->minQuantity = new Decimal(0);
        $this->percent = new Decimal(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPricing(): ?Model\PricingInterface
    {
        return $this->pricing;
    }

    public function setPricing(?Model\PricingInterface $pricing): Model\PricingRuleInterface
    {
        if ($this->pricing === $pricing) {
            return $this;
        }

        if ($previous = $this->pricing) {
            $this->pricing = null;
            $previous->removeRule($this);
        }

        if ($this->pricing = $pricing) {
            $this->pricing->addRule($this);
        }

        return $this;
    }

    public function getMinQuantity(): Decimal
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(Decimal $quantity): Model\PricingRuleInterface
    {
        $this->minQuantity = $quantity;

        return $this;
    }

    public function getPercent(): Decimal
    {
        return $this->percent;
    }

    public function setPercent(Decimal $percent): Model\PricingRuleInterface
    {
        $this->percent = $percent;

        return $this;
    }
}
