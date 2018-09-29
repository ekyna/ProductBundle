<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\PriceRepositoryInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;

/**
 * Class PriceCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceCalculator
{
    /**
     * @var PriceRepositoryInterface
     */
    private $priceRepository;

    /**
     * @var OfferRepositoryInterface
     */
    private $offerRepository;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param PriceRepositoryInterface   $priceRepository
     * @param OfferRepositoryInterface   $offerRepository
     * @param TaxResolverInterface       $taxResolver
     * @param CurrencyConverterInterface $currencyConverter
     * @param string                     $defaultCurrency
     */
    public function __construct(
        PriceRepositoryInterface $priceRepository,
        OfferRepositoryInterface $offerRepository,
        TaxResolverInterface $taxResolver,
        CurrencyConverterInterface $currencyConverter,
        $defaultCurrency
    ) {
        $this->priceRepository = $priceRepository;
        $this->offerRepository = $offerRepository;
        $this->taxResolver = $taxResolver;
        $this->currencyConverter = $currencyConverter;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * Returns the product price for one quantity.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     *
     * @return array
     */
    public function getPrice(Model\ProductInterface $product, ContextInterface $context)
    {
        // Price lookup
        $price = $this
            ->priceRepository
            ->findOneByProductAndContext($product, $context);

        if (null === $price) {
            $amount = $product->getMinPrice();

            $from = Model\ProductTypes::isChildType($product->getType())
                ? $product->hasRequiredOptionGroup()
                : true;

            $price = [
                'starting_from'  => $from,
                'original_price' => null,
                'sell_price'     => $amount,
                'percent'        => 0,
                'details'        => [],
            ];
        }

        $mode = $context->getVatDisplayMode();
        $currency = $context->getCurrency()->getCode();
        $date = $context->getDate();

        // Currency conversion
        if ($currency !== $this->defaultCurrency) {
            if (0 < $amount = $price['original_price']) {
                $price['original_price'] = $this
                    ->currencyConverter
                    ->convert($amount, $this->defaultCurrency, $currency, $date);
            }
            if (0 < $amount = $price['sell_price']) {
                $price['sell_price'] = $this
                    ->currencyConverter
                    ->convert($amount, $this->defaultCurrency, $currency, $date);
            }
        }

        // Taxation
        if ($mode === VatDisplayModes::MODE_ATI) {
            $rates = $this->getTaxesRates($product, $context);

            if (0 < $amount = $price['original_price']) {
                $price['original_price'] = $this->addTaxes($amount, $rates, $currency);
            }
            if (0 < $amount = $price['sell_price']) {
                $price['sell_price'] = $this->addTaxes($amount, $rates, $currency);
            }
        }

        return $price;
    }

    /**
     * @param float   $base
     * @param float[] $taxes
     * @param string  $currency
     *
     * @return float
     */
    private function addTaxes(float $base, array $taxes, string $currency)
    {
        $total = $base;

        foreach ($taxes as $tax) {
            $total += Money::round($base * $tax / 100, $currency);
        }

        return $total;
    }

    /**
     * Calculates the product's minimum price.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $withOptions Whether to add required option groups's cheaper options.
     *
     * @return float|int
     */
    public function calculateMinPrice(Model\ProductInterface $product, $withOptions = true)
    {
        if (Model\ProductTypes::TYPE_CONFIGURABLE === $product->getType()) {
            return $this->calculateConfigurableMinPrice($product, $withOptions);
        }

        if (Model\ProductTypes::TYPE_BUNDLE === $product->getType()) {
            return $this->calculateBundleMinPrice($product, $withOptions);
        }

        if (Model\ProductTypes::TYPE_VARIABLE === $product->getType()) {
            return $this->calculateVariableMinPrice($product, $withOptions);
        }

        return $this->calculateProductMinPrice($product, $withOptions);
    }

    /**
     * Calculates the product min options price.
     *
     * @param Model\ProductInterface $product
     *
     * @return float|int
     */
    public function calculateMinOptionsPrice(Model\ProductInterface $product)
    {
        $price = 0;

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
            $lowestOption = null;
            foreach ($optionGroup->getOptions() as $option) {
                if (null === $optionPrice = $option->getNetPrice()) {
                    // Without product options
                    $optionPrice = $option->getProduct()->getNetPrice();
                }

                if (null === $lowestOption || $optionPrice < $lowestOption) {
                    $lowestOption = $optionPrice;
                }
            }

            // If lowest price found for the option group
            if (null !== $lowestOption) {
                $price += $lowestOption;
            }
        }

        return $price;
    }

    /**
     * Calculates the (simple or variant) product min price.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float|int
     */
    public function calculateProductMinPrice(Model\ProductInterface $product, $withOptions = true)
    {
        Model\ProductTypes::assertChildType($product);

        $price = $product->getNetPrice();

        if ($withOptions) {
            $price += $this->calculateMinOptionsPrice($product);
        }

        return $price;
    }

    /**
     * Calculates the variable product min price.
     *
     * @param Model\ProductInterface $variable
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float|int
     */
    public function calculateVariableMinPrice(Model\ProductInterface $variable, $withOptions = true)
    {
        Model\ProductTypes::assertVariable($variable);

        $price = null;

        foreach ($variable->getVariants() as $variant) {
            if (!$variant->isVisible()) {
                continue;
            }

            $variantPrice = $this->calculateProductMinPrice($variant, $withOptions);

            if (null === $price || $variantPrice < $price) {
                $price = $variantPrice;
            }
        }

        if (is_null($price)) {
            $price = 0;
        }

        return $price;
    }

    /**
     * Calculates the bundle product min price.
     *
     * @param Model\ProductInterface $bundle
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float|int
     *
     * @todo The product (bundle) min price should be processed and persisted during update (flush)
     */
    public function calculateBundleMinPrice(Model\ProductInterface $bundle, $withOptions = true)
    {
        Model\ProductTypes::assertBundle($bundle);

        $price = 0;

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $childProduct = $choice->getProduct();
            $choiceOptions = $withOptions && $choice->isUseOptions();

            if ($childProduct->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                $childPrice = $this->calculateBundleMinPrice($childProduct, $choiceOptions);
            } elseif ($childProduct->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                $childPrice = $this->calculateVariableMinPrice($childProduct, $choiceOptions);
            } else {
                $childPrice = $this->calculateProductMinPrice($childProduct, $choiceOptions);
            }

            // TODO Use packaging format
            $price += $childPrice * $choice->getMinQuantity();
        }

        if ($withOptions) {
            $price += $this->calculateMinOptionsPrice($bundle);
        }

        return $price;
    }

    /**
     * Calculates the configurable product min price.
     *
     * @param Model\ProductInterface $configurable
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float|int
     *
     * @todo The product (configurable) min price should be processed and persisted during update (flush)
     */
    public function calculateConfigurableMinPrice(Model\ProductInterface $configurable, $withOptions = true)
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
            $lowestPrice = null;
            // For each slot choices
            foreach ($slot->getChoices() as $choice) {
                $childProduct = $choice->getProduct();
                $choiceOptions = $withOptions && $choice->isUseOptions();

                // TODO Recurse if parent type (?)

                if ($childProduct->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                    $childPrice = $this->calculateBundleMinPrice($childProduct, $choiceOptions);
                } elseif ($childProduct->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                    $childPrice = $this->calculateVariableMinPrice($childProduct, $choiceOptions);
                } else {
                    $childPrice = $this->calculateProductMinPrice($childProduct, $choiceOptions);
                }

                // TODO Use packaging format
                $childPrice *= $choice->getMinQuantity();

                if (null === $lowestPrice || $childPrice < $lowestPrice) {
                    $lowestPrice = $childPrice;
                }
            }

            $price += $lowestPrice;
        }

        if ($withOptions) {
            $price += $this->calculateMinOptionsPrice($configurable);
        }

        return $price;
    }

    /**
     * Returns the pricing data for the given product and context.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     * @param bool                   $withOffers
     * @param bool                   $withTaxes
     *
     * @return array
     */
    public function buildProductPricing(
        Model\ProductInterface $product,
        ContextInterface $context,
        bool $withOffers = true,
        bool $withTaxes = true
    ) {
        // Net price (bundle : min price without options)
        $amount = $product->getNetPrice();

        // Currency
        $currency = $context->getCurrency()->getCode();
        if ($currency !== $this->defaultCurrency) {
            // Currency conversion
            $amount = $this->currencyConverter->convert($amount, $this->defaultCurrency, $currency);
        }

        $pricing = [
            'price'  => (float)$amount,
            'offers' => null,
            'taxes'  => null,
        ];

        // Offers rules
        if ($withOffers) {
            $offers = $this->getOffers($product, $context);
            foreach ($offers as &$offer) {
                unset($offer['price']);
            }
            $pricing['offers'] = $offers;
        }

        /** @see \Ekyna\Component\Commerce\Common\Resolver\DiscountResolver::resolveSaleItem() */
        // Don't apply discounts/taxes to private items (they will inherit from parents)
        /*if ($item->isPrivate()) {
            return $data;
        }*/

        // Taxes
        if ($withTaxes) {
            $pricing['taxes'] = $this->getTaxesRates($product, $context);
        }

        // Don't apply discount to parent with only public children
        /*if ($item->isCompound() && !$item->hasPrivateChildren()) {
            return $data;
        }*/

        return $pricing;
    }

    /**
     * Returns the pricing grid for the given product and context.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface|null  $context
     *
     * @return array
     */
    public function getPricingGrid(Model\ProductInterface $product, ContextInterface $context)
    {
        $offers = $this->getOffers($product, $context);

        if (empty($offers)) {
            return [];
        }

        $currency = $context->getCurrency()->getCode();

        $price = $product->getNetPrice();

        if ($context->isAtiDisplayMode()) {
            if (!empty($rates = $this->getTaxesRates($product, $context))) {
                foreach ($rates as $rate) {
                    $price *= 1 + $rate / 100;
                }
            }
        }

        if ($currency !== $this->defaultCurrency) {
            // Currency conversion
            $price = $this->currencyConverter->convert($price, $this->defaultCurrency, $currency);
        }

        foreach ($offers as &$offer) {
            $offer['price'] = round($price * (1 - $offer['percent'] / 100), 5);
        }

        return [
            'currency' => $currency,
            'offers'   => $offers,
        ];
    }

    /**
     * Returns the pricing data for the given option and context.
     *
     * @param Model\OptionInterface $option
     * @param ContextInterface      $context
     * @param bool                  $withOffers
     * @param bool                  $withTaxes
     *
     * @return array
     */
    public function buildOptionPricing(
        Model\OptionInterface $option,
        ContextInterface $context,
        bool $withOffers = true,
        bool $withTaxes = true
    ) {
        // Currency
        $currency = $context->getCurrency()->getCode();

        if (null !== $product = $option->getProduct()) {
            $pricing = $this->buildProductPricing($product, $context, $withOffers, $withTaxes);

            // Option's net price override
            if (null !== $price = $option->getNetPrice()) {
                $pricing['price'] = $price;
            }
        } else {
            $pricing = [
                'price'  => (float)$option->getNetPrice(),
                'offers' => [], // Prevent inheritance
                'taxes'  => $withTaxes ? $this->getTaxesRates($option, $context) : [],
            ];
        }

        // Currency conversion
        if (0 < $pricing['price'] && $currency !== $this->defaultCurrency) {
            $pricing['price'] = $this->currencyConverter->convert($pricing['price'], $this->defaultCurrency, $currency);
        }

        return $pricing;
    }

    /**
     * Returns the taxes rates for the given taxable and context.
     *
     * @param TaxableInterface $taxable
     * @param ContextInterface $context
     *
     * @return float[]
     */
    public function getTaxesRates(TaxableInterface $taxable, ContextInterface $context)
    {
        $taxes = [];

        $config = $this->taxResolver->resolveTaxes($taxable, $context->getDeliveryCountry());
        foreach ($config as $tax) {
            $taxes[] = (float)$tax->getRate();
        }

        return $taxes;
    }

    /**
     * Returns the product offers.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     *
     * @return array
     */
    protected function getOffers(Model\ProductInterface $product, ContextInterface $context)
    {
        $offers = $this->offerRepository->findByProductAndContext($product, $context);

        if (!Model\ProductTypes::isBundleType($product)) {
            return $offers;
        }

        $children = [];

        $this->listChildren($children, $product, $context);

        $total = $hidden = 0;

        // Gather min quantities
        $quantities = array_map(function($o) {
            return $o['min_qty'];
        }, $offers);
        foreach ($children as &$child) {
            $total += $child['price'];

            if (null === $child['offers']) {
                $child['offers'] = $offers;
                continue;
            }

            foreach ($child['offers'] as $offer) {
                if (!in_array($offer['min_qty'], $quantities)) {
                    $quantities[] = $offer['min_qty'];
                }
            }
        }
        unset($child);

        sort($quantities);

        $mergedOffers = [];
        foreach ($quantities as $quantity) {
            $discount = 0;

            foreach ($children as $child) {
                foreach ($child['offers'] as $offer) {
                    if ($offer['min_qty'] <= $quantity) {
                        $discount += $child['price'] * $offer['percent'] / 100;
                        continue 2;
                    }
                }
            }

            $mergedOffers[] = [
                'min_qty' => $quantity,
                'percent' => round($discount / $total * 100, 5),
            ];
        }

        return array_reverse($mergedOffers);
    }

    /**
     * List bundle children.
     *
     * @param array                  $list
     * @param Model\ProductInterface $bundle
     * @param ContextInterface       $context
     * @param int                    $qty
     *
     * @return bool
     */
    protected function listChildren(array &$list, Model\ProductInterface $bundle, ContextInterface $context, $qty = 1)
    {
        Model\ProductTypes::assertBundle($bundle);

        $visible = false;

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $product = $choice->getProduct();

            if (Model\ProductTypes::isBundleType($product)) {
                $visible |= $this->listChildren($list, $product, $context, $qty * $choice->getMinQuantity());
            } else {
                $child = [
                    'price'    => $product->getNetPrice() * $qty * $choice->getMinQuantity(),
                    'offers'   => null,
                ];

                if (($product->isVisible() && !$choice->isHidden()) || $product->hasRequiredOptionGroup()) {
                    $visible = true;
                    $child['offers'] = $this->offerRepository->findByProductAndContext($product, $context);
                }

                $list[] = $child;
            }
        }

        return $visible;
    }
}
