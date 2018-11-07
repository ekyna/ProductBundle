<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes as Types;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferResolver;

/**
 * Class Builder
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Builder
{
    /**
     * @var OfferResolver
     */
    protected $offerResolver;


    /**
     * Constructor.
     *
     * @param OfferResolver $offerResolver
     */
    public function __construct(OfferResolver $offerResolver)
    {
        $this->offerResolver = $offerResolver;
    }

    /**
     * Builds the config tree for the given product.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $visible
     * @param bool                   $options
     *
     * @return Tree
     */
    public function build(
        Model\ProductInterface $product,
        bool $visible = true,
        bool $options = true
    ) {
        $tree = new Tree();
        $tree->setVisible($visible);

        if (in_array($product->getType(), [Types::TYPE_VARIABLE, Types::TYPE_CONFIGURABLE], true)) {
            throw new InvalidArgumentException("Unsupported product type.");
        }

        $tree->setOffers($this->resolveOffers($product));

        if (Types::TYPE_BUNDLE === $product->getType()) {
            foreach ($product->getBundleSlots() as $slot) {
                /** @var Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $choiceProduct = $choice->getProduct();

                $child = $this->build(
                    $choiceProduct,
                    $visible && $choiceProduct->isVisible() && !$choice->isHidden(),
                    $options && $choice->isUseOptions()
                );

                $child->setQuantity($choice->getMinQuantity());

                $tree->addChild($child);
            }
        } else {
            $tree->addNetPrice($product->getNetPrice());
        }

        if ($options) {
            $optionGroups = $product->getOptionGroups()->toArray();
            if (Types::TYPE_VARIANT === $product->getType()) {
                $optionGroups = array_merge($optionGroups, $product->getParent()->getOptionGroups()->toArray());
            }
            if (!empty($optionGroups)) {
                $this->buildOptionGroups($tree, $optionGroups);
            }
        }

        return $tree;
    }

    /**
     * Flattens the given tree regarding to the given key.
     *
     * @param Tree   $tree
     * @param string $key
     *
     * @return Result
     */
    public function flatten(Tree $tree, string $key)
    {
        $flat = new Result($key);

        $price = $tree->getNetPrice();

        $flat
            ->addOriginalPrice($price)
            ->addBasePrice($price);

        $this->flattenTree($flat, $tree);

        $price = $flat->getBasePrice();

        if (0 < $price && !is_null($offer = $tree->getBestOffer($flat->getKey()))) {
            // Store discount amount for each type
            foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
                if (!isset($offer['details'][$type])) {
                    continue;
                }

                $discount = round($price * $offer['details'][$type] / 100, 5);
                $flat->addDiscount($type, $discount);
                $price -= $discount;
            }
        }

        $flat->addSellPrice($price);

        return $flat;
    }

    protected function flattenTree(Result $flat, Tree $tree)
    {
        $visible = $tree->getVisible();

        foreach ($tree->getChildren() as $child) {
            $price = $child->getNetPrice() * $child->getTotalQuantity();

            $flat->addOriginalPrice($price);

            // If child is visible (results in a not private sale item)
            if ($this->flattenTree($flat, $child)) {
                $visible = true;

                // Pick offer regarding to key
                if (null !== $offer = $child->getBestOffer($flat->getKey())) {
                    // Store discount amount for each type
                    foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
                        if (!isset($offer['details'][$type])) {
                            continue;
                        }

                        $discount = round($price * $offer['details'][$type] / 100, 5);
                        $flat->addDiscount($type, $discount);
                        $price -= $discount;
                    }
                }
                $flat->addSellPrice($price);
            } else {
                // Else if child is hidden (results in a private sale item)
                $flat->addBasePrice($price);
            }
        }

        if (!empty($optionGroups = $tree->getOptionGroups())) {
            $visible = true;

            foreach ($tree->getOptionGroups() as $group) {
                $bestOChild = $bestOPrice = null;
                $bestSChild = $bestSPrice = null;
                $bestDiscounts = [];

                // Find best option
                foreach ($group->getOptions() as $option) {
                    $oPrice = $option->getNetPrice() * $tree->getTotalQuantity();

                    // Best original price
                    if (is_null($bestOChild) || $bestOPrice > $oPrice) {
                        $bestOChild = $option;
                        $bestOPrice = $oPrice;
                    }

                    // If option has no offers for this group/country couple, continue to next option
                    if (null === $offer = $option->getOffer($flat->getKey())) {
                        continue;
                    }

                    // TODO compare with tree root offer

                    // If option has the best sell price
                    $sPrice = round($oPrice * (1 - $offer['percent'] / 100), 5);
                    if (is_null($bestSChild) || $bestSPrice > $sPrice) {
                        $bestSChild = $option;
                        $bestSPrice = $sPrice;

                        $bestDiscounts = [];

                        // Store discount amount for each type
                        $base = $oPrice;
                        foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
                            if (!isset($offer['details'][$type])) {
                                continue;
                            }

                            $discount = round($oPrice * $offer['details'][$type] / 100, 5);
                            $bestDiscounts[$type] = $discount;
                            $base -= $discount;
                        }
                    }
                }

                if (is_null($bestOPrice)) {
                    $bestOPrice = 0;
                }
                if (is_null($bestSPrice)) {
                    $bestSPrice = $bestOPrice;
                }

                $flat->addOriginalPrice($bestOPrice);
                $flat->addSellPrice($bestSPrice);

                // Add best option's discount amount for each types
                foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
                    if (isset($bestDiscounts[$type])) {
                        $flat->addDiscount($type, $bestDiscounts[$type]);
                    }
                }
            }
        }

        return $visible;
    }

    /**
     * Builds the options groups.
     *
     * @param Tree                         $tree
     * @param Model\OptionGroupInterface[] $optionGroups
     */
    protected function buildOptionGroups(Tree $tree, array $optionGroups)
    {
        /** @var Model\OptionGroupInterface $optionGroup */
        foreach ($optionGroups as $optionGroup) {
            if (!$optionGroup->isRequired()) {
                continue;
            }

            $og = new OptionGroup();

            foreach ($optionGroup->getOptions() as $option) {
                $item = new Item(0);

                if (null !== $product = $option->getProduct()) {
                    $item->setOffers($this->resolveOffers($product));
                    $item->setNetPrice($product->getNetPrice());
                }

                if (null !== $option->getNetPrice()) {
                    $item->setNetPrice($option->getNetPrice());
                }

                $og->addOption($item);
            }

            $tree->addOptionGroup($og);
        }
    }


    /**
     * Resolves the product offers.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    protected function resolveOffers(Model\ProductInterface $product)
    {
        $offers = [];

        foreach ($this->offerResolver->resolve($product) as &$offer) {
            if (1 != $offer['min_qty']) {
                continue;
            }

            $offers[$this->getKey($offer)] = $offer;
        }

        return $offers;
    }

    /**
     * Returns the key (<group_id>-<country_id>).
     *
     * @param array $data
     *
     * @return string
     */
    protected function getKey(array $data)
    {
        return sprintf('%d-%d', $data['group_id'], $data['country_id']);
    }
}
