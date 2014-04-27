<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Component\Sale\Product\OptionGroupInterface;
use Ekyna\Component\Sale\Product\OptionInterface;

/**
 * OptionGroup.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OptionGroup implements OptionGroupInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var integer
     */
    protected $position;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = array();
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
     * Returns the identifier.
     * 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     * 
     * @return OptionGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the position.
     *
     * @param integer $position
     * 
     * @return OptionGroup
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Adds an option (non-mapped field).
     *
     * @param \Ekyna\Component\Sale\Product\OptionInterface $option
     */
    public function addOption(OptionInterface $option)
    {
        $this->options[] = $option;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOptions()
    {
        return 0 < count($this->options);
    }

	/**
	 * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }
}
