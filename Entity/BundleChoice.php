<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class BundleChoice
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoice implements Model\BundleChoiceInterface
{
    use SortableTrait;

    protected ?int                       $id                   = null;
    protected ?Model\BundleSlotInterface $slot                 = null;
    protected ?Model\ProductInterface    $product              = null;
    protected Decimal                    $minQuantity;
    protected Decimal                    $maxQuantity;
    protected array                      $excludedOptionGroups = [];
    protected ?Decimal                   $netPrice             = null;
    protected bool                       $hidden               = true;
    protected bool                       $excludeImages        = true;
    /** @var Collection<Model\BundleChoiceRuleInterface> */
    protected Collection $rules;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
        $this->minQuantity = new Decimal(1);
        $this->maxQuantity = new Decimal(1);
    }

    public function __clone()
    {
        $this->id = null;
        $this->slot = null;

        $rules = $this->rules->toArray();
        $this->rules = new ArrayCollection();
        foreach ($rules as $rule) {
            $this->addRule(clone $rule);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlot(): ?Model\BundleSlotInterface
    {
        return $this->slot;
    }

    public function setSlot(?Model\BundleSlotInterface $slot): Model\BundleChoiceInterface
    {
        if ($this->slot === $slot) {
            return $this;
        }

        if ($previous = $this->slot) {
            $this->slot = null;
            $previous->removeChoice($this);
        }

        if ($this->slot = $slot) {
            $this->slot->addChoice($this);
        }

        return $this;
    }

    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?Model\ProductInterface $product): Model\BundleChoiceInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getMinQuantity(): Decimal
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(Decimal $quantity): Model\BundleChoiceInterface
    {
        $this->minQuantity = $quantity;

        return $this;
    }

    public function getMaxQuantity(): Decimal
    {
        return $this->maxQuantity;
    }

    public function setMaxQuantity(Decimal $quantity): Model\BundleChoiceInterface
    {
        $this->maxQuantity = $quantity;

        return $this;
    }

    public function getExcludedOptionGroups(): array
    {
        return $this->excludedOptionGroups;
    }

    public function setExcludedOptionGroups(array $ids): Model\BundleChoiceInterface
    {
        $this->excludedOptionGroups = $ids;

        return $this;
    }

    public function getNetPrice(): ?Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(?Decimal $price): Model\BundleChoiceInterface
    {
        $this->netPrice = $price;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): Model\BundleChoiceInterface
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function hasRule(Model\BundleChoiceRuleInterface $rule): bool
    {
        return $this->rules->contains($rule);
    }

    public function addRule(Model\BundleChoiceRuleInterface $rule): Model\BundleChoiceInterface
    {
        if (!$this->hasRule($rule)) {
            $this->rules->add($rule);
            $rule->setChoice($this);
        }

        return $this;
    }

    public function removeRule(Model\BundleChoiceRuleInterface $rule): Model\BundleChoiceInterface
    {
        if ($this->hasRule($rule)) {
            $this->rules->removeElement($rule);
            $rule->setChoice(null);
        }

        return $this;
    }

    public function setRules(Collection $rules): Model\BundleChoiceInterface
    {
        $this->rules = $rules;

        return $this;
    }

    public function isExcludeImages(): bool
    {
        return $this->excludeImages;
    }

    public function setExcludeImages(bool $exclude): Model\BundleChoiceInterface
    {
        $this->excludeImages = $exclude;

        return $this;
    }
}
