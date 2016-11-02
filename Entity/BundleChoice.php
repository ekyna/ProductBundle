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
     * @var integer
     */
    protected $minQuantity;

    /**
     * @var integer
     */
    protected $maxQuantity;

    /**
     * @var Model\OptionInterface
     */
    /* TODO option per group */
    // protected $option;

    /**
     * @var bool
     */
    protected $userOption;

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
        $this->rules = new ArrayCollection();
    }

    /**
     * Clones the bundle choice.
     */
    public function __clone()
    {
        if ($this->id) {
            $rules = $this->rules;
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
        $this->slot = $slot;

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
    public function isUserOption()
    {
        return $this->userOption;
    }

    /**
     * @inheritdoc
     */
    public function setUserOption($userOption)
    {
        $this->userOption = $userOption;

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
            $rule->setChoice($this);
            $this->rules->add($rule);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeRule(Model\BundleChoiceRuleInterface $rule)
    {
        if ($this->hasRule($rule)) {
            $rule->setChoice(null);
            $this->rules->removeElement($rule);
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
