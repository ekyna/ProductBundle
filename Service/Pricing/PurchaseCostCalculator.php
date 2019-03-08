<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesserInterface;

/**
 * Class CostCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PurchaseCostCalculator
{
    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;

    /**
     * @var PurchaseCostGuesserInterface
     */
    protected $costGuesser;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param PriceCalculator              $priceCalculator
     * @param PurchaseCostGuesserInterface $guesser
     * @param string                       $defaultCurrency
     */
    public function __construct(
        PriceCalculator $priceCalculator,
        PurchaseCostGuesserInterface $guesser,
        string $defaultCurrency
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->costGuesser = $guesser;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * Calculates the product minimum purchase cost.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $withOptions Whether to add options min cost.
     *
     * @return float|int
     */
    public function calculateMinPurchaseCost(Model\ProductInterface $product, $withOptions = true)
    {
        if (Model\ProductTypes::isConfigurableType($product)) {
            return $this->calculateConfigurablePurchaseCost($product, $withOptions);
        }

        if (Model\ProductTypes::isBundleType($product)) {
            return $this->calculateBundlePurchaseCost($product, $withOptions);
        }

        if (Model\ProductTypes::isVariableType($product)) {
            return $this->calculateVariablePurchaseCost($product, $withOptions);
        }

        return $this->calculateProductPurchaseCost($product, $withOptions);
    }

    /**
     * Calculates the product min options purchase cost.
     *
     * @param Model\ProductInterface $product
     *
     * @return float|int
     */
    public function calculateMinOptionsPurchaseCost(Model\ProductInterface $product)
    {
        $cost = 0;

        $optionGroups = $product->getOptionGroups()->toArray();

        if ($product->getType() === Model\ProductTypes::TYPE_VARIANT) {
            $optionGroups = array_merge($optionGroups, $product->getParent()->getOptionGroups()->toArray());
        }

        // For each option groups
        /** @var \Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface $optionGroup */
        foreach ($optionGroups as $optionGroup) {
            // Skip non required option group
            if (!$optionGroup->isRequired()) {
                continue;
            }

            // Get option with lowest price
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
            if (null !== $lowestOption) {
                if ($op = $lowestOption->getProduct()) {
                    $cost += $this->costGuesser->guess($op, $this->defaultCurrency);
                }
            }
        }

        return $cost;
    }

    /**
     * Calculates the simple/variant product minimum purchase cost.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $withOptions Whether to add options min cost.
     *
     * @return float|int
     */
    protected function calculateProductPurchaseCost(Model\ProductInterface $product, $withOptions = true)
    {
        Model\ProductTypes::assertChildType($product);

        $price = $this->costGuesser->guess($product, $this->defaultCurrency);

        if ($withOptions) {
            $price += $this->calculateMinOptionsPurchaseCost($product);
        }

        return $price;
    }

    /**
     * Calculates the variable product minimum purchase cost.
     *
     * @param Model\ProductInterface $variable
     * @param bool                   $withOptions Whether to add options min cost.
     *
     * @return float|int
     */
    protected function calculateVariablePurchaseCost(Model\ProductInterface $variable, $withOptions = true)
    {
        Model\ProductTypes::assertVariable($variable);

        /** @var Model\ProductInterface $lowestVariant */
        $lowestVariant = null;
        foreach ($variable->getVariants() as $variant) {
            if (!$variant->isVisible()) {
                continue;
            }

            $variantPrice = $this->priceCalculator->calculateProductMinPrice($variant, $withOptions);

            if (null === $lowestVariant || $lowestVariant->getMinPrice() > $variantPrice) {
                $lowestVariant = $variant;
            }
        }

        if ($lowestVariant) {
            return $this->calculateProductPurchaseCost($lowestVariant, $withOptions);
        }

        return 0;
    }

    /**
     * Calculates the variable bundle minimum purchase cost.
     *
     * @param Model\ProductInterface $bundle
     * @param bool                   $withOptions Whether to add options min cost.
     *
     * @return float|int
     */
    protected function calculateBundlePurchaseCost(Model\ProductInterface $bundle, $withOptions = true)
    {
        Model\ProductTypes::assertBundle($bundle);

        $price = 0;
        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $choiceOptions = $withOptions && $choice->isUseOptions();

            $price += $this->calculateMinPurchaseCost($choice->getProduct(), $choiceOptions)
                * $choice->getMinQuantity(); // TODO Use packaging format
        }

        if ($withOptions) {
            $price += $this->calculateMinOptionsPurchaseCost($bundle);
        }

        return $price;
    }

    /**
     * Calculates the variable configurable minimum purchase cost.
     *
     * @param Model\ProductInterface $configurable
     * @param bool                   $withOptions Whether to add options min cost.
     *
     * @return float|int
     */
    protected function calculateConfigurablePurchaseCost(Model\ProductInterface $configurable, $withOptions = true)
    {
        Model\ProductTypes::assertConfigurable($configurable);

        $price = 0;

        // For each bundle slots
        foreach ($configurable->getBundleSlots() as $slot) {
            // Skip non required slots
            if (!$slot->isRequired()) {
                continue;
            }

            // Get slot choice with lowest price.
            $lowestChoice = $lowestPrice = null;
            // For each slot choices
            foreach ($slot->getChoices() as $choice) {
                $childProduct = $choice->getProduct();
                $choiceOptions = $withOptions && $choice->isUseOptions();

                if ($childProduct->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                    $choicePrice = $this->priceCalculator->calculateBundleMinPrice($childProduct, $choiceOptions);
                } elseif ($childProduct->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                    $choicePrice = $this->priceCalculator->calculateVariableMinPrice($childProduct, $choiceOptions);
                } else {
                    $choicePrice = $this->priceCalculator->calculateProductMinPrice($childProduct, $choiceOptions);
                }

                // TODO Use packaging format
                $choicePrice *= $choice->getMinQuantity();

                if (null === $lowestChoice || $lowestPrice > $choicePrice) {
                    $lowestPrice = $choicePrice;
                    $lowestChoice = $choice;
                }
            }

            if ($lowestChoice) {
                $price += $this->calculateMinPurchaseCost(
                    $lowestChoice->getProduct(), $withOptions && $lowestChoice->isUseOptions()
                ) * $lowestChoice->getMinQuantity(); // TODO Use packaging format
            }
        }

        if ($withOptions) {
            $price += $this->calculateMinOptionsPurchaseCost($configurable);
        }

        return $price;
    }
}