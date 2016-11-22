<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class Option
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\OptionTranslationInterface translate($locale = null, $create = false)
 */
class Option extends RM\AbstractTranslatable implements Model\OptionInterface
{
    use RM\SortableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\OptionGroupInterface
     */
    protected $group;

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
     * @var TaxGroupInterface
     */
    protected $taxGroup;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDesignation();
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
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(Model\OptionGroupInterface $group = null)
    {
        $this->group = $group;

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
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function setWeight($weight)
    {
        $this->weight = (float)$weight;

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
    public function setNetPrice($netPrice)
    {
        $this->netPrice = (float)$netPrice;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxGroup()
    {
        return $this->taxGroup;
    }

    /**
     * @inheritdoc
     */
    public function setTaxGroup(TaxGroupInterface $group)
    {
        $this->taxGroup = $group;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getTranslationClass()
    {
        return OptionTranslation::class;
    }
}
