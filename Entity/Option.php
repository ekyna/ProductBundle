<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model as RM;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class Option
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\OptionTranslationInterface translate($locale = null, $create = false)
 *
 * @TODO Rename to 'OptionValue' or 'OptionChoice'
 */
class Option extends RM\AbstractTranslatable implements Model\OptionInterface, GroupSequenceProviderInterface
{
    use RM\SortableTrait;
    use TaxableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\OptionGroupInterface
     */
    protected $group;

    /**
     * @var Model\ProductInterface
     */
    protected $product;

    /**
     * @var bool
     */
    protected $cascade = false;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $weight;

    /**
     * @var float
     */
    protected $netPrice;


    /**
     * Clones the option.
     */
    public function __clone()
    {
        parent::__clone();

        $this->id = null;
        $this->group = null;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->designation ?: 'New option';
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
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(Model\OptionGroupInterface $group = null)
    {
        if ($this->group !== $group) {
            if ($previous = $this->group) {
                $this->group = null;
                $previous->removeOption($this);
            }

            if ($this->group = $group) {
                $this->group->addOption($this);
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
    public function setProduct(Model\ProductInterface $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Returns whether option product's options should be added to the form (add to sale)..
     *
     * @return bool
     */
    public function isCascade()
    {
        return $this->cascade;
    }

    /**
     * Sets whether option product's options should be added to the form (add to sale).
     *
     * @param bool $cascade
     *
     * @return Option
     */
    public function setCascade(bool $cascade)
    {
        $this->cascade = $cascade;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

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
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($netPrice = null)
    {
        $this->netPrice = $netPrice;

        return $this;
    }

    /**
     * Returns the mode (for the option form type).
     *
     * @return string
     */
    public function getMode()
    {
        return null !== $this->product ? 'product' : 'data';
    }

    /**
     * Fake setter (for the option form type).
     *
     * @param $mode
     */
    public function setMode($mode) {/* Do nothing. */}

    /**
     * @inheritDoc
     */
    public function getGroupSequence()
    {
        $groups = ['Option'];

        if (null !== $this->product) {
            $groups[] = 'product';
        } else {
            $groups[] = 'data';
        }

        return $groups;
    }

    /**
     * @inheritDoc
     */
    protected function getTranslationClass(): string
    {
        return OptionTranslation::class;
    }
}
