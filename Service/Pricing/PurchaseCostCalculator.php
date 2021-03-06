<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface as Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
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
     * @param Product    $product
     * @param bool|array $exclude  The option group ids to exclude, true to exclude all
     * @param bool       $shipping Whether to include shipping cost
     *
     * @return float
     */
    public function calculateMinPurchaseCost(Product $product, $exclude = [], bool $shipping = false): float
    {
        if (ProductTypes::isConfigurableType($product)) {
            return $this->calculateConfigurablePurchaseCost($product, $exclude, $shipping);
        }

        if (ProductTypes::isBundleType($product)) {
            return $this->calculateBundlePurchaseCost($product, $exclude, $shipping);
        }

        if (ProductTypes::isVariableType($product)) {
            return $this->calculateVariablePurchaseCost($product, $exclude, $shipping);
        }

        return $this->calculateProductPurchaseCost($product, $exclude, $shipping);
    }

    /**
     * Calculates the product min options purchase cost.
     *
     * @param Product    $product
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     * @param bool       $shipping Whether to include shipping cost
     *
     * @return float
     */
    protected function calculateMinOptionsPurchaseCost(Product $product, $exclude = [], bool $shipping = false): float
    {
        $cost = 0;

        $optionGroups = $product->resolveOptionGroups($exclude);

        // For each option groups
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
                    $cost += $this->costGuesser->guess($op, $this->defaultCurrency, $shipping);
                }
            }
        }

        return $cost;
    }

    /**
     * Calculates the product component's purchase cost.
     *
     * @param Product $product
     * @param bool       $shipping Whether to include shipping cost
     *
     * @return float
     */
    protected function calculateComponentsPurchaseCost(Product $product, bool $shipping = false): float
    {
        $total = 0;

        foreach ($product->getComponents() as $component) {
            $cost = $this->costGuesser->guess($component->getChild(), $this->defaultCurrency, $shipping);

            $total += $cost * $component->getQuantity();
        }

        return $total;
    }

    /**
     * Calculates the simple/variant product minimum purchase cost.
     *
     * @param Product    $product
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     * @param bool       $shipping Whether to include shipping cost
     *
     * @return float
     */
    protected function calculateProductPurchaseCost(Product $product, $exclude = [], bool $shipping = false): float
    {
        ProductTypes::assertChildType($product);

        $price = $this->costGuesser->guess($product, $this->defaultCurrency, $shipping);

        $price += $this->calculateMinOptionsPurchaseCost($product, $exclude, $shipping);

        $price += $this->calculateComponentsPurchaseCost($product, $shipping);

        return $price;
    }

    /**
     * Calculates the variable product minimum purchase cost.
     *
     * @param Product    $variable
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     * @param bool       $shipping Whether to include shipping cost
     *
     * @return float
     */
    protected function calculateVariablePurchaseCost(Product $variable, $exclude = [], bool $shipping = false): float
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

        if ($lowestVariant) {
            return $this->calculateProductPurchaseCost($lowestVariant, $exclude, $shipping)
                + $this->calculateComponentsPurchaseCost($variable, $shipping);
        }

        return 0;
    }

    /**
     * Calculates the variable bundle minimum purchase cost.
     *
     * @param Product    $bundle
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     * @param bool       $shipping Whether to include shipping cost
     *
     * @return float
     */
    protected function calculateBundlePurchaseCost(Product $bundle, $exclude = [], bool $shipping = false): float
    {
        ProductTypes::assertBundle($bundle);

        $total = 0;
        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            if (true === $exclude) {
                $choiceExclude = true;
            } else {
                $choiceExclude = array_unique(array_merge(
                    is_array($exclude) ? $exclude : [],
                    $choice->getExcludedOptionGroups()
                ));
            }

            $cost = $this->calculateMinPurchaseCost($choice->getProduct(), $choiceExclude, $shipping);

            $total += $cost * $choice->getMinQuantity(); // TODO Use packaging format
        }

        $total += $this->calculateMinOptionsPurchaseCost($bundle, $exclude, $shipping);

        $total += $this->calculateComponentsPurchaseCost($bundle, $shipping);

        return $total;
    }

    /**
     * Calculates the variable configurable minimum purchase cost.
     *
     * @param Product    $configurable
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     * @param bool       $shipping Whether to include shipping cost
     *
     * @return float
     */
    protected function calculateConfigurablePurchaseCost(
        Product $configurable,
        $exclude = [],
        bool $shipping = false
    ): float {
        ProductTypes::assertConfigurable($configurable);

        $total = 0;

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
                        ->calculateBundleMinPrice($childProduct, $choiceExclude, $shipping);
                } elseif ($childProduct->getType() === ProductTypes::TYPE_VARIABLE) {
                    $choicePrice = $this
                        ->priceCalculator
                        ->calculateVariableMinPrice($childProduct, $choiceExclude, $shipping);
                } else {
                    $choicePrice = $this
                        ->priceCalculator
                        ->calculateProductMinPrice($childProduct, $choiceExclude, $shipping);
                }

                // TODO Use packaging format
                $choicePrice *= $choice->getMinQuantity();

                if (null === $lowestChoice || $lowestPrice > $choicePrice) {
                    $lowestPrice = $choicePrice;
                    $lowestChoice = $choice;
                }
            }

            if ($lowestChoice) {
                if (true === $exclude) {
                    $choiceExclude = true;
                } else {
                    $choiceExclude = array_unique(array_merge(
                        is_array($exclude) ? $exclude : [],
                        $lowestChoice->getExcludedOptionGroups()
                    ));
                }

                $cost = $this->calculateMinPurchaseCost($lowestChoice->getProduct(), $choiceExclude, $shipping);

                $total += $cost * $lowestChoice->getMinQuantity(); // TODO Use packaging format
            }
        }

        $total += $this->calculateMinOptionsPurchaseCost($configurable, $exclude);

        $total += $this->calculateComponentsPurchaseCost($configurable);

        return $total;
    }
}
