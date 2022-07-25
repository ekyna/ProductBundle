<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

use DateTime;
use Decimal\Decimal;
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
    public function __construct(private readonly OfferResolver $offerResolver)
    {
    }

    /**
     * Builds the config tree for the given product.
     *
     * @param array $exclude The option group ids to exclude
     */
    public function build(
        Model\ProductInterface $product,
        bool                   $visible = true,
        array                  $exclude = [],
        Decimal                $netPrice = null
    ): Tree {
        $tree = new Tree();
        $tree->setVisible($visible);

        if (in_array($product->getType(), [Types::TYPE_VARIABLE, Types::TYPE_CONFIGURABLE], true)) {
            throw new InvalidArgumentException('Unsupported product type.');
        }

        $tree->setOffers($this->resolveOffers($product));

        if (Types::TYPE_BUNDLE === $product->getType()) {
            $this->buildBundleSlots($tree, $product, $visible, $exclude);
            $this->buildComponents($tree, $product);
        } elseif (!is_null($netPrice)) {
            $tree->addNetPrice($netPrice);
        } else {
            $tree->addNetPrice(clone $product->getNetPrice());
            if (Model\ProductTypes::isVariantType($product)) {
                $this->buildComponents($tree, $product->getParent());
            } else {
                $this->buildComponents($tree, $product);
            }
        }

        if (!empty($optionGroups = $product->resolveOptionGroups($exclude, true))) {
            $this->buildOptionGroups($tree, $optionGroups);
        }

        return $tree;
    }

    /**
     * Builds the product's bundle slots tree children.
     *
     * @param array $exclude The option group ids to exclude
     */
    protected function buildBundleSlots(
        Tree                   $tree,
        Model\ProductInterface $product,
        bool                   $visible = true,
        array                  $exclude = []
    ): void {
        foreach ($product->getBundleSlots() as $slot) {
            /** @var Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $choiceProduct = $choice->getProduct();

            $child = $this->build(
                $choiceProduct,
                $visible && $choiceProduct->isVisible() && !$choice->isHidden(),
                array_unique(array_merge($exclude, $choice->getExcludedOptionGroups())),
                $choice->getNetPrice()
            );

            $child->setQuantity($choice->getMinQuantity());

            $tree->addChild($child);
        }
    }

    /**
     * Builds the product's components tree children.
     */
    protected function buildComponents(Tree $tree, Model\ProductInterface $product): void
    {
        foreach ($product->getComponents() as $component) {
            $price = is_null($component->getNetPrice())
                ? clone $component->getChild()->getNetPrice()
                : clone $component->getNetPrice();

            $child = new Tree();
            $child
                ->setVisible(false)
                ->setNetPrice($price)
                ->setOffers($this->resolveOffers($component->getChild()))
                ->setQuantity(clone $component->getQuantity());

            $tree->addChild($child);
        }
    }

    /**
     * Builds the options groups.
     *
     * @param array<Model\OptionGroupInterface> $optionGroups
     */
    protected function buildOptionGroups(Tree $tree, array $optionGroups): void
    {
        foreach ($optionGroups as $optionGroup) {
            if (!$optionGroup->isRequired()) {
                continue;
            }

            $og = new OptionGroup();

            foreach ($optionGroup->getOptions() as $option) {
                $item = new Item();

                if ($product = $option->getProduct()) {
                    $item->setOffers($this->resolveOffers($product));
                    $item->setNetPrice($product->getNetPrice());
                }

                if ($option->getNetPrice()) {
                    $item->setNetPrice($option->getNetPrice());
                }

                $og->addOption($item);
            }

            $tree->addOptionGroup($og);
        }
    }

    /**
     * Resolves the product offers.
     */
    protected function resolveOffers(Model\ProductInterface $product): array
    {
        $offers = [];

        foreach ($this->offerResolver->resolve($product) as $offer) {
            if (1 != $offer['min_qty']) {
                continue;
            }

            $offers[$this->getKey($offer)] = $offer;
        }

        return $offers;
    }

    /**
     * Returns the key (<group_id>-<country_id>).
     */
    protected function getKey(array $data): string
    {
        return sprintf('%d-%d', $data['group_id'], $data['country_id']);
    }

    /**
     * Flattens the given tree regarding the given key.
     */
    public function flatten(Tree $tree, string $key): Result
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
            foreach ([Model\OfferInterface::TYPE_SPECIAL, Model\OfferInterface::TYPE_PRICING] as $type) {
                if (!isset($offer['details'][$type])) {
                    continue;
                }

                $discount = $price->mul($offer['details'][$type])->div(100)->round(5);
                $flat->addDiscount($type, $discount);
                $price -= $discount;

                if (isset($offer['ends_at'])) {
                    $flat->addEndsAt(new DateTime($offer['ends_at']));
                }
            }
        }

        $flat->addSellPrice($price);

        return $flat;
    }

    protected function flattenTree(Result $flat, Tree $tree): bool
    {
        $visible = $tree->getVisible();

        foreach ($tree->getChildren() as $child) {
            $price = $child->getNetPrice()->mul($child->getTotalQuantity());

            $flat->addOriginalPrice($price);

            // If child is visible (results in a not private sale item)
            if ($this->flattenTree($flat, $child)) {
                $visible = true;

                // Pick offer regarding key
                if (null !== $offer = $child->getBestOffer($flat->getKey())) {
                    // Store discount amount for each type
                    foreach ([Model\OfferInterface::TYPE_SPECIAL, Model\OfferInterface::TYPE_PRICING] as $type) {
                        if (!isset($offer['details'][$type])) {
                            continue;
                        }

                        $discount = $price->mul($offer['details'][$type])->div(100)->round(5);
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

        if (empty($optionGroups = $tree->getOptionGroups())) {
            return $visible;
        }

        $flat->setStartingFrom(true);

        foreach ($optionGroups as $group) {
            $bestOChild = $bestOPrice = null;
            $bestSChild = $bestSPrice = null;
            $bestDiscounts = [];

            // Find best option
            foreach ($group->getOptions() as $option) {
                $oPrice = $option->getNetPrice()->mul($tree->getTotalQuantity());

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
                $sPrice = $oPrice->mul((string)(1 - $offer['percent'] / 100))->round(5);
                if (is_null($bestSChild) || $bestSPrice > $sPrice) {
                    $bestSChild = $option;
                    $bestSPrice = $sPrice;

                    $bestDiscounts = [];

                    // Store discount amount for each type
                    $base = $oPrice;
                    foreach ([Model\OfferInterface::TYPE_SPECIAL, Model\OfferInterface::TYPE_PRICING] as $type) {
                        if (!isset($offer['details'][$type])) {
                            continue;
                        }

                        $discount = $oPrice->mul((string)($offer['details'][$type] / 100))->round(5);
                        $bestDiscounts[$type] = $discount;
                        $base -= $discount;
                    }
                }
            }

            if (is_null($bestOPrice)) {
                $bestOPrice = new Decimal(0);
            }
            if (is_null($bestSPrice)) {
                $bestSPrice = $bestOPrice;
            }

            $flat->addOriginalPrice($bestOPrice);
            $flat->addSellPrice($bestSPrice);

            // Add best option's discount amount for each types
            foreach ([Model\OfferInterface::TYPE_SPECIAL, Model\OfferInterface::TYPE_PRICING] as $type) {
                if (isset($bestDiscounts[$type])) {
                    $flat->addDiscount($type, $bestDiscounts[$type]);
                }
            }
        }

        return true;
    }
}
