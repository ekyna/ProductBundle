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
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->choices = new ArrayCollection();
        $this->required = true;
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
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->translate()->getTitle();
    }

    /**
     * @inheritdoc
     */
    /*public function setTitle($title)
    {
        $this->translate()->setTitle($title);

        return $this;
    }*/

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }

    /**
     * @inheritdoc
     */
    /*public function setDescription($description)
    {
        $this->translate()->setDescription($description);

        return $this;
    }*/

    /**
     * @inheritdoc
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @inheritdoc
     */
    public function hasChoice(Model\BundleChoiceInterface $choice)
    {
        return $this->choices->contains($choice);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * Returns the required.
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Sets the required.
     *
     * @param bool $required
     *
     * @return BundleSlot
     */
    public function setRequired($required)
    {
        $this->required = (bool)$required;

        return $this;
    }
}
