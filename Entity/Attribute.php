<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model AS RM;

/**
 * Class AttributeGroup
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\AttributeTranslationInterface translate($locale = null, $create = false)
 */
class Attribute extends RM\AbstractTranslatable implements Model\AttributeInterface
{
    use RM\SortableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ArrayCollection|Model\AttributeChoiceInterface[]
     */
    protected $choices;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->choices = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getName();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

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
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @inheritdoc
     */
    public function hasChoice(Model\AttributeChoiceInterface $choice)
    {
        return $this->choices->contains($choice);
    }

    /**
     * @inheritdoc
     */
    public function addChoice(Model\AttributeChoiceInterface $choice)
    {
        if (!$this->hasChoice($choice)) {
            $this->choices->add($choice);
            $choice->setAttribute($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChoice(Model\AttributeChoiceInterface $choice)
    {
        if ($this->hasChoice($choice)) {
            $this->choices->removeElement($choice);
            $choice->setAttribute(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setChoices(ArrayCollection $choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return AttributeTranslation::class;
    }
}
