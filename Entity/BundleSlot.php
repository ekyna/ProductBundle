<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model\AbstractTranslatable;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class BundleSlot
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\BundleSlotTranslationInterface translate($locale = null, $create = false)
 */
class BundleSlot extends AbstractTranslatable implements Model\BundleSlotInterface
{
    use MediaSubjectTrait,
        SortableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\ProductInterface
     */
    protected $bundle;

    /**
     * @var ArrayCollection|Model\BundleChoiceInterface[]
     */
    protected $choices;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var ArrayCollection|Model\BundleSlotRuleInterface[]
     */
    protected $rules;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->choices = new ArrayCollection();
        $this->required = true;
        $this->rules = new ArrayCollection();
    }

    /**
     * Clones the bundle slot.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->bundle = null;

            $choices = $this->choices->toArray();
            $this->choices = new ArrayCollection();
            foreach ($choices as $choice) {
                $this->addChoice(clone $choice);
            }
            
            $rules = $this->rules->toArray();
            $this->rules = new ArrayCollection();
            foreach ($rules as $rule) {
                $this->addRule(clone $rule);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @inheritDoc
     */
    public function setBundle(Model\ProductInterface $bundle = null)
    {
        if ($this->bundle !== $bundle) {
            if ($previous = $this->bundle) {
                $this->bundle = null;
                $previous->removeBundleSlot($this);
            }

            if ($this->bundle = $bundle) {
                $this->bundle->addBundleSlot($this);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->translate()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title)
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }

    /**
     * @inheritDoc
     */
    public function setDescription(string $description)
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @inheritDoc
     */
    public function hasChoice(Model\BundleChoiceInterface $choice)
    {
        return $this->choices->contains($choice);
    }

    /**
     * @inheritDoc
     */
    public function addChoice(Model\BundleChoiceInterface $choice)
    {
        if (!$this->hasChoice($choice)) {
            $this->choices->add($choice);
            $choice->setSlot($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeChoice(Model\BundleChoiceInterface $choice)
    {
        if ($this->hasChoice($choice)) {
            $this->choices->removeElement($choice);
            $choice->setSlot(null);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @inheritDoc
     */
    public function setRequired($required)
    {
        $this->required = (bool)$required;

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
    public function hasRule(Model\BundleSlotRuleInterface $rule)
    {
        return $this->rules->contains($rule);
    }

    /**
     * @inheritDoc
     */
    public function addRule(Model\BundleSlotRuleInterface $rule)
    {
        if (!$this->hasRule($rule)) {
            $this->rules->add($rule);
            $rule->setSlot($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeRule(Model\BundleSlotRuleInterface $rule)
    {
        if ($this->hasRule($rule)) {
            $this->rules->removeElement($rule);
            $rule->setSlot(null);
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
}
