<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class OptionGroup
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroup implements Model\OptionGroupInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\ProductInterface
     */
    protected $product;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var ArrayCollection|Model\OptionInterface[]
     */
    protected $options;

    /**
     * @var integer
     */
    protected $position;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    /**
     * Clones the option group.
     */
    public function __clone()
    {
        if ($this->id) {
            $options = $this->options;
            $this->options = new ArrayCollection();
            foreach ($options as $option) {
                $this->addOption(clone $option);
            }
        }
    }

    /**
     * Returns the string representation.
     *
     * @return string
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(Model\ProductInterface $product = null)
    {
        $this->product = $product;
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
    }

    /**
     * @inheritdoc
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @inheritdoc
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function hasOption(Model\OptionInterface $option)
    {
        return $this->options->contains($option);
    }

    /**
     * @inheritdoc
     */
    public function addOption(Model\OptionInterface $option)
    {
        if (!$this->hasOption($option)) {
            $option->setGroup($this);
            $this->options->add($option);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOption(Model\OptionInterface $option)
    {
        if ($this->hasOption($option)) {
            $option->setGroup(null);
            $this->options->removeElement($option);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(ArrayCollection $options)
    {
        $this->options = $options;

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
    }
}
