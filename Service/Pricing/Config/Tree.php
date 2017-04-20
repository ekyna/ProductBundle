<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

use Decimal\Decimal;

/**
 * Class Tree
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Tree extends Item
{
    protected ?Tree $parent= null;
    /** @var array<Tree> */
    protected array $children = [];
    /** @var array<OptionGroup> */
    protected array $optionGroups = [];

    public function getRoot(): Tree
    {
        $root = $this;

        while ($root->hasParent()) {
            $root = $root->getParent();
        }

        return $root;
    }

    public function getParent(): ?Tree
    {
        return $this->parent;
    }

    public function setParent(Tree $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    public function addChild(Tree $child): self
    {
        $child->setParent($this);

        $this->children[] = $child;

        return $this;
    }

    /**
     * @return array<Tree>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addOptionGroup(OptionGroup $group): self
    {
        $this->optionGroups[] = $group;

        return $this;
    }

    /**
     * @return array<OptionGroup>
     */
    public function getOptionGroups(): array
    {
        return $this->optionGroups;
    }

    public function getTotalQuantity(): Decimal
    {
        $qty = $this->quantity;

        $parent = $this;
        while ($parent = $parent->getParent()) {
            $qty *= $parent->getQuantity();
        }

        return $qty;
    }

    /**
     * Returns the tree best offer for the given key (regarding root ones).
     */
    public function getBestOffer(string $key): ?array
    {
        $offer = null;
        $item = $this;

        do {
            if (is_null($o = $item->getOffer($key))) {
                continue;
            }
            if (is_null($offer) || $o['percent'] >= $offer['percent']) {
                $offer = $o;
            }
        } while ($item = $item->getParent());

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
