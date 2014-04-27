<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Sale\Product\OptionInterface;
use Ekyna\Component\Sale\Product\ProductInterface;
use Ekyna\Component\Sale\Product\ProductTypes;

/**
 * AbstractProduct.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractProduct implements ProductInterface
{
    use \Ekyna\Component\Sale\PriceableTrait;
    use \Ekyna\Component\Sale\ReferenceableTrait;
    use \Ekyna\Component\Sale\WeighableTrait;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $type;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $options;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = ProductTypes::PHYSICAL;
        $this->options = new ArrayCollection();
    }

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
     * Returns the identifier.
     * 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the type
     * 
     * @param integer $type
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns wether the product has options or not.
     * 
     * @return boolean
     */
    public function hasOptions()
    {
        return 0 < $this->options->count();
    }

    /**
     * Returns wether the product has the given option or not.
     * 
     * @param \Ekyna\Component\Sale\Product\OptionInterface $option
     * 
     * @return boolean
     */
    public function hasOption(OptionInterface $option)
    {
        return $this->options->contains($option);
    }

    /**
     * Adds an option.
     *
     * @param \Ekyna\Component\Sale\Product\OptionInterface $option
     * 
     * @return \Ekyna\Bundle\ProductBundle\Entity\AbstractProduct
     */
    public function addOption(OptionInterface $option)
    {
        $option->setProduct($this);
        $this->options->add($option);
    
        return $this;
    }

    /**
     * Removes an option.
     *
     * @param \Ekyna\Component\Sale\Product\OptionInterface $option
     */
    public function removeOption(OptionInterface $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsGroups()
    {
        $groups = new ArrayCollection();
        foreach ($this->options as $option) {
            if (! $groups->contains($group = $option->getGroup())) {
                $groups->add($group);
            }
        }
        return $groups;
    }
}
