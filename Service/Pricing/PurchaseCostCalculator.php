<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;

use function array_key_exists;
use function array_merge;
use function array_unique;
use function implode;
use function is_array;

/**
 * Class CostCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PurchaseCostCalculator
{
    /**
     * @var array<string, Cost>
     */
    private array $productCache = [];
    private array $optionsCache = [];

    public function __construct(
        protected readonly PriceCalculator             $priceCalculator,
        protected readonly SubjectCostGuesserInterface $costGuesser,
    ) {
    }

    public function onClear(): void
    {
        $this->productCache = [];
        $this->optionsCache = [];
    }

    protected function cacheKey(Product $product, array|bool $exclude): string
    {
        $exclude = is_array($exclude) ? implode('-', $exclude) : ($exclude ? 't' : 'f' );

        return sprintf('%d_%s', $product->getId(), $exclude);
    }

    /**
     * Calculates the product minimum purchase cost.
     *
     * @param Product    $product
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @return Cost
     */
    public function calculateMinPurchaseCost(Product $product, array|bool $exclude = []): Cost
    {
        $key = $this->cacheKey($product, $exclude);

        if (array_key_exists($key, $this->productCache)) {
            return $this->productCache[$key];
        }

        if (ProductTypes::isConfigurableType($product)) {
            return $this->productCache[$key] = $this->calculateConfigurablePurchaseCost($product, $exclude);
        }

        if (ProductTypes::isBundleType($product)) {
            return $this->productCache[$key] = $this->calculateBundlePurchaseCost($product, $exclude);
        }

        if (ProductTypes::isVariableType($product)) {
            return $this->productCache[$key] = $this->calculateVariablePurchaseCost($product, $exclude);
        }

        return $this->productCache[$key] = $this->calculateProductPurchaseCost($product, $exclude);
    }

    /**
     * Calculates the product min options purchase cost.
     *
     * @param Product    $product
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @return Cost
     */
    protected function calculateMinOptionsPurchaseCost(Product $product, array|bool $exclude): Cost
    {
        $key = $this->cacheKey($product, $exclude);

        if (array_key_exists($key, $this->optionsCache)) {
            return $this->optionsCache[$key];
        }

        $cost = new Cost();

        $optionGroups = $product->resolveOptionGroups($exclude);

        // For each option groups
        foreach ($optionGroups as $optionGroup) {
            // Skip non required option group
            if (!$optionGroup->isRequired()) {
                continue;
            }

            // Get option with the lowest price
            $lowestPrice = null;
            $lowestOption = null;
            foreach ($optionGroup->getOptions() as $option) {
                if (null === $optionPrice = $option->getNetPrice()) {
                    // Without product options
                    $optionPrice = $option->getProduct()->getNetPrice();
                }

                if (null === $lowestPrice || $optionPrice < $lowestPrice) {
                    $lowestPrice = $optionPrice;
                    $lowestOption = $option;
                }
            }

            // If lowest price found for the option group
            if (null === $lowestOption) {
                continue;
            }

            if (null === $optionProduct = $lowestOption->getProduct()) {
                continue;
            }

            $cost->add($this->calculateMinPurchaseCost($optionProduct, true));
        }

        return $this->optionsCache[$key] = $cost;
    }

    /**
     * Calculates the product component's purchase cost.
     *
     * @param Product $product
     *
     * @return Cost
     */
    protected function calculateComponentsPurchaseCost(Product $product): Cost
    {
        $total = new Cost();

        foreach ($product->getComponents() as $component) {
            $cost = $this->costGuesser->guess($component->getChild());

            $cost->multiply($component->getQuantity()); // TODO Use packaging format

            $total->add($cost);
        }

        return $total;
    }

    /**
     * Calculates the simple/variant product minimum purchase cost.
     *
     * @param Product    $product
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @return Cost
     */
    protected function calculateProductPurchaseCost(Product $product, array|bool $exclude = []): Cost
    {
        ProductTypes::assertChildType($product);

        $cost = $this->costGuesser->guess($product) ?: new Cost();

        $cost->add($this->calculateMinOptionsPurchaseCost($product, $exclude));

        $cost->add($this->calculateComponentsPurchaseCost($product));

        return $cost;
    }

    /**
     * Calculates the variable product minimum purchase cost.
     *
     * @param Product    $variable
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @return Cost
     */
    protected function calculateVariablePurchaseCost(Product $variable, array|bool $exclude = []): Cost
    {
        ProductTypes::assertVariable($variable);

        /** @var Product $lowestVariant */
        $lowestVariant = null;
        foreach ($variable->getVariants() as $variant) {
            if (!$variant->isVisible()) {
                continue;
            }

            $variantPrice = $this->priceCalculator->calculateProductMinPrice($variant, $exclude);

            if (null === $lowestVariant || $lowestVariant->getMinPrice() > $variantPrice) {
                $lowestVariant = $variant;
            }
        }

        $cost = new Cost();

        if (null === $lowestVariant) {
            return $cost;
        }

        $cost->add($this->calculateProductPurchaseCost($lowestVariant, $exclude));

        $cost->add($this->calculateComponentsPurchaseCost($variable));

        return $cost;
    }

    /**
     * Calculates the variable bundle minimum purchase cost.
     *
     * @param Product    $bundle
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @return Cost
     */
    protected function calculateBundlePurchaseCost(Product $bundle, array|bool $exclude = []): Cost
    {
        ProductTypes::assertBundle($bundle);

        $total = new Cost();
        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();

            $this->addBundleChoiceCost($total, $choice, $exclude);
        }

        $total->add($this->calculateMinOptionsPurchaseCost($bundle, $exclude));

        $total->add($this->calculateComponentsPurchaseCost($bundle));

        return $total;
    }

    /**
     * Calculates the variable configurable minimum purchase cost.
     *
     * @param Product    $configurable
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @return Cost
     */
    protected function calculateConfigurablePurchaseCost(Product $configurable, array|bool $exclude = []): Cost
    {
        ProductTypes::assertConfigurable($configurable);

        $total = new Cost();

        // For each bundle slots
        foreach ($configurable->getBundleSlots() as $slot) {
            // Skip non required slots
            if (!$slot->isRequired()) {
                continue;
            }

            // Get slot choice with the lowest price.
            $lowestChoice = $lowestPrice = null;
            // For each slot choices
            foreach ($slot->getChoices() as $choice) {
                $childProduct = $choice->getProduct();
                if (true === $exclude) {
                    $choiceExclude = true;
                } else {
                    $choiceExclude = array_unique(array_merge(
                        is_array($exclude) ? $exclude : [],
                        $choice->getExcludedOptionGroups()
                    ));
                }

                if ($childProduct->getType() === ProductTypes::TYPE_BUNDLE) {
                    $choicePrice = $this
                        ->priceCalculator
                        ->calculateBundleMinPrice($childProduct, $choiceExclude);
                } elseif ($childProduct->getType() === ProductTypes::TYPE_VARIABLE) {
                    $choicePrice = $this
                        ->priceCalculator
                        ->calculateVariableMinPrice($childProduct, $choiceExclude);
                } else {
                    $choicePrice = $this
                        ->priceCalculator
                        ->calculateProductMinPrice($childProduct, $choiceExclude);
                }

                $choicePrice *= $choice->getMinQuantity(); // TODO Use packaging format

                if (null === $lowestChoice || $lowestPrice > $choicePrice) {
                    $lowestPrice = $choicePrice;
                    $lowestChoice = $choice;
                }
            }

            if (null === $lowestChoice) {
                continue;
            }

            $this->addBundleChoiceCost($total, $lowestChoice, $exclude);
        }

        $total->add($this->calculateMinOptionsPurchaseCost($configurable, $exclude));

        $total->add($this->calculateComponentsPurchaseCost($configurable));

        return $total;
    }

    /**
     * @param Cost                  $total
     * @param BundleChoiceInterface $choice
     * @param bool|array            $exclude
     * @return void
     */
    private function addBundleChoiceCost(Cost $total, BundleChoiceInterface $choice, bool|array $exclude): void
    {
        if (true !== $exclude) {
            $exclude = array_unique(array_merge(
                is_array($exclude) ? $exclude : [],
                $choice->getExcludedOptionGroups()
            ));
        }

        $cost = $this->calculateMinPurchaseCost($choice->getProduct(), $exclude);

        $cost->multiply($choice->getMinQuantity()); // TODO Use packaging format

        $total->add($cost);
    }
}
