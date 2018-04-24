<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class BundleChoice
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoice implements Model\BundleChoiceInterface
{
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
     * @var bool
     */
    protected $useOptions;

    /**
     * @var bool
     */
    protected $hidden;

    /**
     * @var ArrayCollection|Model\BundleChoiceRuleInterface[]
     */
    protected $rules;

    /**
     * @var integer
     */
    protected $position;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->minQuantity = 1;
        $this->maxQuantity = 1;
        $this->useOptions = true;
        $this->hidden = false;
        $this->rules = new ArrayCollection();
    }

    /**
     * Clones the bundle choice.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->slot = null;

            $rules = $this->rules->toArray();
            $this->rules = new ArrayCollection();
            foreach ($rules as $rule) {
                $this->addRule(clone $rule);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(Model\ProductInterface $product)
    {
        $this->product = $product;

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
        $this->minQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    /**
     * @inheritdoc
     */
    public function setMaxQuantity($quantity)
    {
        $this->maxQuantity = $quantity;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isUseOptions()
    {
        return $this->useOptions;
    }

    /**
     * @inheritdoc
     */
    public function setUseOptions($use)
    {
        $this->useOptions = (bool)$use;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @inheritdoc
     */
    public function setHidden($hidden)
    {
        $this->hidden = (bool)$hidden;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @inheritdoc
     */
    public function hasRule(Model\BundleChoiceRuleInterface $rule)
    {
        return $this->rules->contains($rule);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function setRules(ArrayCollection $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
