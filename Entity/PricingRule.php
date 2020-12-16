<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class PricingRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRule implements Model\PricingRuleInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\PricingInterface
     */
    protected $pricing;

    /**
     * @var int
     */
    protected $minQuantity;

    /**
     * @var float
     */
    protected $percent;


    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getPricing()
    {
        return $this->pricing;
    }

    /**
     * @inheritdoc
     */
    public function setPricing(Model\PricingInterface $pricing = null)
    {
        if ($this->pricing !== $pricing) {
            if ($previous = $this->pricing) {
                $this->pricing = null;
                $previous->removeRule($this);
            }

            if ($this->pricing = $pricing) {
                $this->pricing->addRule($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setMinQuantity($quantity)
    {
        $this->minQuantity = (int)$quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @inheritdoc
     */
    public function setPercent($percent)
    {
        $this->percent = (float)$percent;

        return $this;
    }
}
