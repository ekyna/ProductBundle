<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class OptionGroup
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\OptionGroupTranslationInterface translate($locale = null, $create = false)
 *
 * @TODO Rename to 'Option'
 */
class OptionGroup extends RM\AbstractTranslatable implements Model\OptionGroupInterface
{
    use RM\SortableTrait;

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
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->options = new ArrayCollection();
        $this->required = false;
    }

    /**
     * Clones the option group.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->product = null;

            $options = $this->options->toArray();
            $this->options = new ArrayCollection();
            foreach ($options as $option) {
                $this->addOption(clone $option);
            }

            $translations = $this->translations->toArray();
            $this->translations = new ArrayCollection();
            foreach ($translations as $translation) {
                $this->addTranslation(clone $translation);
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

        return $this;
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
    public function getTitle()
    {
        return $this->translate()->getTitle();
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
        $this->required = (bool)$required;

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
     * @inheritDoc
     */
    protected function getTranslationClass()
    {
        return OptionGroupTranslation::class;
    }
}
