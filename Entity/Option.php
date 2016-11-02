<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class Option
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Option implements Model\OptionInterface
{
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
    protected $netPrice;

    /**
     * @var TaxGroupInterface
     */
    protected $taxGroup;

    /**
     * @var integer
     */
    protected $position;


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
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;

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
