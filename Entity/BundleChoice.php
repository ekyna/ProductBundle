<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\BundleSlotInterface
     */
    protected $slot;

    /**
     * @var Model\ProductInterface
     */
    protected $product;

    /**
     * @var float
     */
    protected $minQuantity;

    /**
     * @var float
     */
    protected $maxQuantity;

    /**
     * @var array
     */
    protected $excludedOptionGroups;

    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var bool
     */
    protected $hidden;

    /**
     * @var ArrayCollection|Model\BundleChoiceRuleInterface[]
     */
    protected $rules;

    /**
     * @var bool
     */
    protected $excludeImages;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->minQuantity = 1;
        $this->maxQuantity = 1;
        $this->excludedOptionGroups = [];
        $this->hidden = true;
        $this->excludeImages = true;
        $this->rules = new ArrayCollection();
    }

    /**
     * Clones the bundle choice.
     */
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

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @inheritDoc
     */
    public function setSlot(Model\BundleSlotInterface $slot = null)
    {
        if ($this->slot !== $slot) {
            if ($previous = $this->slot) {
                $this->slot = null;
                $previous->removeChoice($this);
            }

            if ($this->slot = $slot) {
                $this->slot->addChoice($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritDoc
     */
    public function setProduct(Model\ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setMinQuantity($quantity)
    {
        $this->minQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    /**
     * @inheritDoc
     */
    public function setMaxQuantity($quantity)
    {
        $this->maxQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExcludedOptionGroups()
    {
        return $this->excludedOptionGroups;
    }

    /**
     * @inheritDoc
     */
    public function setExcludedOptionGroups(array $ids)
    {
        $this->excludedOptionGroups = $ids;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritDoc
     */
    public function setNetPrice($price)
    {
        $this->netPrice = $price;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @inheritDoc
     */
    public function setHidden($hidden)
    {
        $this->hidden = (bool)$hidden;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @inheritDoc
     */
    public function hasRule(Model\BundleChoiceRuleInterface $rule)
    {
        return $this->rules->contains($rule);
    }

    /**
     * @inheritDoc
     */
    public function addRule(Model\BundleChoiceRuleInterface $rule)
    {
        if (!$this->hasRule($rule)) {
            $this->rules->add($rule);
            $rule->setChoice($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeRule(Model\BundleChoiceRuleInterface $rule)
    {
        if ($this->hasRule($rule)) {
            $this->rules->removeElement($rule);
            $rule->setChoice(null);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRules(ArrayCollection $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isExcludeImages(): bool
    {
        return $this->excludeImages;
    }

    /**
     * @inheritDoc
     */
    public function setExcludeImages(bool $exclude): Model\BundleChoiceInterface
    {
        $this->excludeImages = $exclude;

        return $this;
    }
}
