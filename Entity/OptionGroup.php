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
     * @var int
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
     * @var bool
     */
    protected $fullTitle;

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
        $this->fullTitle = false;
    }

    /**
     * Clones the option group.
     */
    public function __clone()
    {
        parent::__clone();

        $this->id = null;
        $this->product = null;

        $options = $this->options->toArray();
        $this->options = new ArrayCollection();
        foreach ($options as $option) {
            $this->addOption(clone $option);
        }
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New option group';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
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
        if ($this->product !== $product) {
            if ($previous = $this->product) {
                $this->product = null;
                $previous->removeOptionGroup($this);
            }

            if ($this->product = $product) {
                $this->product->addOptionGroup($this);
            }
        }

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
    public function setTitle(string $title)
    {
        $this->translate()->setTitle($title);

        return $this;
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
    public function isFullTitle()
    {
        return $this->fullTitle;
    }

    /**
     * @inheritdoc
     */
    public function setFullTitle(bool $full)
    {
        $this->fullTitle = $full;

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
            $this->options->add($option);
            $option->setGroup($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOption(Model\OptionInterface $option)
    {
        if ($this->hasOption($option)) {
            $this->options->removeElement($option);
            $option->setGroup(null);
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
    protected function getTranslationClass(): string
    {
        return OptionGroupTranslation::class;
    }
}
