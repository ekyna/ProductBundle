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
     * Returns the tree root.
     *
     * @return Tree
     */
    public function getRoot(): Tree
    {
        $root = $this;

        while ($root->hasParent()) {
            $root = $root->getParent();
        }

        return $root;
    }

    /**
     * Returns the parent.
     *
     * @return Tree
     */
    public function getParent(): ?Tree
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
    public function setParent(Tree $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Returns whether the tree as a parent.
     *
     * @return bool
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent);
    }

    /**
     * Adds the child.
     *
     * @param Tree $child
     *
     * @return $this
     */
    public function addChild(Tree $child): self
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
    public function getChildren(): array
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
    public function addOptionGroup(OptionGroup $group): self
    {
        $this->optionGroups[] = $group;

        return $this;
    }

    /**
     * Returns the option groups.
     *
     * @return OptionGroup[]
     */
    public function getOptionGroups(): array
    {
        return $this->optionGroups;
    }

    /**
     * Returns the total quantity.
     *
     * @return float
     */
    public function getTotalQuantity(): float
    {
        $qty = $this->quantity;

        $parent = $this;
        while (null !== $parent = $parent->getParent()) {
            $qty *= $parent->getQuantity();
        }

        return $qty;
    }

    /**
     * Returns the tree best offer for the given key (regarding to root ones).
     *
     * @param string $key
     *
     * @return array|null
     */
    public function getBestOffer(string $key): ?array
    {
        $offer = null;
        $item = $this;

        do {
            if (is_null($o = $item->getOffer($key))) {
                continue;
            }
            if (is_null($offer) || 0 <= bccomp($o['percent'], $offer['percent'], 2)) {
                $offer = $o;
            }
        } while($item = $item->getParent());

        return $offer;
    }

    /**
     * Returns
     *
     * @return array
     */
    public function getKeys(): array
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
