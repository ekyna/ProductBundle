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
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float|int
     */
    public function calculateMinPurchaseCost(Model\ProductInterface $product, $exclude = [])
    {
        if (Model\ProductTypes::isConfigurableType($product)) {
            return $this->calculateConfigurablePurchaseCost($product, $exclude);
        }

        if (Model\ProductTypes::isBundleType($product)) {
            return $this->calculateBundlePurchaseCost($product, $exclude);
        }

        if (Model\ProductTypes::isVariableType($product)) {
            return $this->calculateVariablePurchaseCost($product, $exclude);
        }

        return $this->calculateProductPurchaseCost($product, $exclude);
    }

    /**
     * Calculates the product min options purchase cost.
     *
     * @param Model\ProductInterface $product
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float|int
     */
    public function calculateMinOptionsPurchaseCost(Model\ProductInterface $product, $exclude = [])
    {
        $cost = 0;

        $optionGroups = $product->resolveOptionGroups($exclude);

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
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float|int
     */
    protected function calculateProductPurchaseCost(Model\ProductInterface $product, $exclude = [])
    {
        Model\ProductTypes::assertChildType($product);

        $price = $this->costGuesser->guess($product, $this->defaultCurrency);

        $price += $this->calculateMinOptionsPurchaseCost($product, $exclude);

        return $price;
    }

    /**
     * Calculates the variable product minimum purchase cost.
     *
     * @param Model\ProductInterface $variable
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float|int
     */
    protected function calculateVariablePurchaseCost(Model\ProductInterface $variable, $exclude = [])
    {
        Model\ProductTypes::assertVariable($variable);

        /** @var Model\ProductInterface $lowestVariant */
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
            return $this->calculateProductPurchaseCost($lowestVariant, $exclude);
        }

        return 0;
    }

    /**
     * Calculates the variable bundle minimum purchase cost.
     *
     * @param Model\ProductInterface $bundle
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float|int
     */
    protected function calculateBundlePurchaseCost(Model\ProductInterface $bundle, $exclude = [])
    {
        Model\ProductTypes::assertBundle($bundle);

        $price = 0;
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

            $price += $this->calculateMinPurchaseCost($choice->getProduct(), $choiceExclude)
                    * $choice->getMinQuantity(); // TODO Use packaging format
        }

        $price += $this->calculateMinOptionsPurchaseCost($bundle, $exclude);

        return $price;
    }

    /**
     * Calculates the variable configurable minimum purchase cost.
     *
     * @param Model\ProductInterface $configurable
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float|int
     */
    protected function calculateConfigurablePurchaseCost(Model\ProductInterface $configurable, $exclude = [])
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
                if (true === $exclude) {
                    $choiceExclude = true;
                } else {
                    $choiceExclude = array_unique(array_merge(
                        is_array($exclude) ? $exclude : [],
                        $choice->getExcludedOptionGroups()
                    ));
                }

                if ($childProduct->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                    $choicePrice = $this->priceCalculator->calculateBundleMinPrice($childProduct, $choiceExclude);
                } elseif ($childProduct->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                    $choicePrice = $this->priceCalculator->calculateVariableMinPrice($childProduct, $choiceExclude);
                } else {
                    $choicePrice = $this->priceCalculator->calculateProductMinPrice($childProduct, $choiceExclude);
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

                $price += $this->calculateMinPurchaseCost($lowestChoice->getProduct(), $choiceExclude)
                        * $lowestChoice->getMinQuantity(); // TODO Use packaging format
            }
        }

        $price += $this->calculateMinOptionsPurchaseCost($configurable, $exclude);

        return $price;
    }
}