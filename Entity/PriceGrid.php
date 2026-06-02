<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model\PriceGridInterface;
use Ekyna\Bundle\ProductBundle\Model\PriceGridRuleInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class PriceGrid
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceGrid extends AbstractResource implements PriceGridInterface
{
    private ?ProductInterface $product = null;
    private ?CustomerGroupInterface $group = null;
    /** @var Collection<PriceGridRuleInterface> */
    private Collection $rules;


    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): PriceGridInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getGroup(): ?CustomerGroupInterface
    {
        return $this->group;
    }

    public function setGroup(?CustomerGroupInterface $group): PriceGridInterface
    {
        $this->group = $group;

        return $this;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function hasRule(PriceGridRuleInterface $rule): bool
    {
        return $this->rules->contains($rule);
    }

    public function addRule(PriceGridRuleInterface $rule): PriceGridInterface
    {
        if (!$this->hasRule($rule)) {
            $this->rules->add($rule);
            $rule->setGrid($this);
        }

        return $this;
    }

    public function removeRule(PriceGridRuleInterface $rule): PriceGridInterface
    {
        if ($this->hasRule($rule)) {
            $this->rules->removeElement($rule);
            $rule->setGrid(null);
        }

        return $this;
    }
}
