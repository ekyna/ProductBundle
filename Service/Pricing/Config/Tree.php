<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

/**
 * Class Tree
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Tree extends Item
{
    /**
     * @var Tree
     */
    protected $parent;

    /**
     * @var Tree[]
     */
    protected $children = [];

    /**
     * @var OptionGroup[]
     */
    protected $optionGroups = [];


    /**
     * Returns the parent.
     *
     * @return Tree
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent.
     *
     * @param Tree $parent
     *
     * @return Tree
     */
    public function setParent(Tree $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Adds the child.
     *
     * @param Tree $child
     *
     * @return $this
     */
    public function addChild(Tree $child)
    {
        $child->setParent($this);

        $this->children[] = $child;

        return $this;
    }

    /**
     * Returns the children.
     *
     * @return Tree[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Adds the option group.
     *
     * @param OptionGroup $group
     *
     * @return $this
     */
    public function addOptionGroup(OptionGroup $group)
    {
        $this->optionGroups[] = $group;

        return $this;
    }

    /**
     * Returns the option groups.
     *
     * @return OptionGroup[]
     */
    public function getOptionGroups()
    {
        return $this->optionGroups;
    }

    /**
     * Returns the total quantity.
     *
     * @return float
     */
    public function getTotalQuantity()
    {
        $qty = $this->quantity;

        $parent = $this;
        while (null !== $parent = $parent->getParent()) {
            $qty *= $parent->getQuantity();
        }

        return $qty;
    }

    /**
     * Returns
     *
     * @return array
     */
    public function getKeys()
    {
        $keys = array_keys($this->offers);

        foreach ($this->getChildren() as $child) {
            $keys = array_unique(array_merge($keys, $child->getKeys()));
        }

        foreach ($this->optionGroups as $optionGroup) {
            foreach ($optionGroup->getOptions() as $option) {
                $keys = array_unique(array_merge($keys, array_keys($option->getOffers())));
            }
        }

        return $keys;
    }
}
