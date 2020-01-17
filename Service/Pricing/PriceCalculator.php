<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes as Types;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\PriceRepositoryInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
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
        string $defaultCurrency
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
    public function getPrice(Model\ProductInterface $product, ContextInterface $context): array
    {
        // Price lookup
        $price = $this
            ->priceRepository
            ->findOneByProductAndContext($product, $context);

        if (null === $price) {
            $amount = $product->getMinPrice();

            if (in_array($product->getType(), [Types::TYPE_CONFIGURABLE, Types::TYPE_VARIABLE], true)) {
                $from = true;
            } else {
                $from = $product->hasRequiredOptionGroup();
            }

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
        } else {
            if (0 < $amount = $price['original_price']) {
                $price['original_price'] = Money::round($amount, $currency);
            }
            if (0 < $amount = $price['sell_price']) {
                $price['sell_price'] = Money::round($amount, $currency);
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
    private function addTaxes(float $base, array $taxes, string $currency): float
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
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float
     */
    public function calculateMinPrice(Model\ProductInterface $product, $exclude = []): float
    {
        if (Types::isConfigurableType($product)) {
            return $this->calculateConfigurableMinPrice($product, $exclude);
        }

        if (Types::isBundleType($product)) {
            return $this->calculateBundleMinPrice($product, $exclude);
        }

        if (Types::isVariableType($product)) {
            return $this->calculateVariableMinPrice($product, $exclude);
        }

        return $this->calculateProductMinPrice($product, $exclude);
    }

    /**
     * Calculates the product min options price.
     *
     * @param Model\ProductInterface $product
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float
     */
    protected function calculateMinOptionsPrice(Model\ProductInterface $product, $exclude = []): float
    {
        if (true === $exclude) {
            return 0;
        }

        $price = 0;

        $optionGroups = $product->resolveOptionGroups($exclude);

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

                if (is_null($lowestOption) || $optionPrice < $lowestOption) {
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
     * Calculates the components total price.
     *
     * @param Model\ProductInterface $product
     *
     * @return float
     */
    public function calculateComponentsPrice(Model\ProductInterface $product): float
    {
        $price = 0;

        foreach ($product->getComponents() as $component) {
            if (is_null($p = $component->getNetPrice())) {
                $p = $component->getChild()->getNetPrice();
            }

            $price += $p * $component->getQuantity();
        }

        return $price;
    }

    /**
     * Calculates the (simple or variant) product min price.
     *
     * @param Model\ProductInterface $product
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     * @param float                  $price   Price override.
     *
     * @return float
     */
    public function calculateProductMinPrice(Model\ProductInterface $product, $exclude = [], $price = null): float
    {
        Types::assertChildType($product);

        if (is_null($price)) {
            $price = $product->getNetPrice();
        }

        $price += $this->calculateMinOptionsPrice($product, $exclude);

        $price += $this->calculateComponentsPrice($product);

        return $price;
    }

    /**
     * Calculates the variable product min price.
     *
     * @param Model\ProductInterface $variable
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     * @param float                  $price   Price override.
     *
     * @return float
     */
    public function calculateVariableMinPrice(
        Model\ProductInterface $variable,
        $exclude = [],
        float $price = null
    ): float {
        Types::assertVariable($variable);

        if (!is_null($price)) {
            return $price + $this->calculateMinOptionsPrice($variable, $exclude);
        }

        $lowestVariant = null;
        foreach ($variable->getVariants() as $variant) {
            $variantPrice = $this->calculateProductMinPrice($variant, $exclude);

            if (null === $lowestVariant || $variantPrice < $lowestVariant) {
                $lowestVariant = $variantPrice;
            }

            if (!$variant->isVisible()) {
                continue;
            }

            if (null === $price || $variantPrice < $price) {
                $price = $variantPrice;
            }
        }

        if (is_null($price)) {
            $price = $lowestVariant;
        }

        $price += $this->calculateComponentsPrice($variable);

        return $price;
    }

    /**
     * Calculates the bundle product min price.
     *
     * @param Model\ProductInterface $bundle
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     * @param float                  $price   Price override.
     *
     * @return float
     *
     * @todo The product (bundle) min price should be processed and persisted during update (flush)
     */
    public function calculateBundleMinPrice(Model\ProductInterface $bundle, $exclude = [], $price = null): float
    {
        Types::assertBundle($bundle);

        if (is_null($price)) {
            $price = 0;
            foreach ($bundle->getBundleSlots() as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $childProduct = $choice->getProduct();
                $choicePrice = $choice->getNetPrice();

                if (true === $exclude) {
                    $choiceExclude = $exclude;
                } else {
                    $choiceExclude = array_unique(array_merge(
                        is_array($exclude) ? $exclude : [],
                        $choice->getExcludedOptionGroups()
                    ));
                }

                if ($childProduct->getType() === Types::TYPE_BUNDLE) {
                    $childPrice = $this->calculateBundleMinPrice($childProduct, $choiceExclude, $choicePrice);
                } elseif ($childProduct->getType() === Types::TYPE_VARIABLE) {
                    $childPrice = $this->calculateVariableMinPrice($childProduct, $choiceExclude, $choicePrice);
                } else {
                    $childPrice = $this->calculateProductMinPrice($childProduct, $choiceExclude, $choicePrice);
                }

                // TODO Use packaging format
                $price += $childPrice * $choice->getMinQuantity();
            }
        }

        $price += $this->calculateMinOptionsPrice($bundle, $exclude);

        return $price;
    }

    /**
     * Calculates the configurable product min price.
     *
     * @param Model\ProductInterface $configurable
     * @param bool|array             $exclude The option group ids to exclude, true to exclude all
     *
     * @return float
     *
     * @todo The product (configurable) min price should be processed and persisted during update (flush)
     */
    public function calculateConfigurableMinPrice(Model\ProductInterface $configurable, $exclude = []): float
    {
        Types::assertConfigurable($configurable);

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
                if (true === $exclude) {
                    $choiceExclude = $exclude;
                } else {
                    $choiceExclude = array_unique(array_merge(
                        is_array($exclude) ? $exclude : [],
                        $choice->getExcludedOptionGroups()
                    ));
                }

                if ($childProduct->getType() === Types::TYPE_BUNDLE) {
                    $childPrice = $this->calculateBundleMinPrice($childProduct, $choiceExclude);
                } elseif ($childProduct->getType() === Types::TYPE_VARIABLE) {
                    $childPrice = $this->calculateVariableMinPrice($childProduct, $choiceExclude);
                } else {
                    $childPrice = $this->calculateProductMinPrice($childProduct, $choiceExclude);
                }

                // TODO Use packaging format
                $childPrice *= $choice->getMinQuantity();

                if (null === $lowestPrice || $childPrice < $lowestPrice) {
                    $lowestPrice = $childPrice;
                }
            }

            $price += $lowestPrice;
        }

        $price += $this->calculateMinOptionsPrice($configurable, $exclude);

        $price += $this->calculateComponentsPrice($configurable);

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
    ): array {
        // Net price (bundle : min price without options)
        $amount = $product->getNetPrice();

        if (Types::isVariantType($product)) {
            $amount += $product->getParent()->getNetPrice();
        }

        // Currency
        $currency = $context->getCurrency()->getCode();
        if ($currency !== $this->defaultCurrency) {
            // Currency conversion
            $amount = $this
                ->currencyConverter
                ->convert($amount, $this->defaultCurrency, $currency, $context->getDate());
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
    public function getPricingGrid(Model\ProductInterface $product, ContextInterface $context): array
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
            $price = $this
                ->currencyConverter
                ->convert($price, $this->defaultCurrency, $currency, $context->getDate());
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
    ): array {
        if (null !== $product = $option->getProduct()) {
            $pricing = $this->buildProductPricing($product, $context, $withOffers, $withTaxes);

            // Option's net price override
            if (null !== $price = $option->getNetPrice()) {
                $pricing['price'] = $this
                    ->currencyConverter
                    ->convert(
                        $price,
                        $this->defaultCurrency,
                        $context->getCurrency()->getCode(),
                        $context->getDate()
                    );
            }

            return $pricing;
        }

        $price = $this
            ->currencyConverter
            ->convert(
                (float)$option->getNetPrice(),
                $this->defaultCurrency,
                $context->getCurrency()->getCode(),
                $context->getDate()
            );

        return [
            'price'  => $price,
            'offers' => [], // Prevent inheritance
            'taxes'  => $withTaxes ? $this->getTaxesRates($option, $context) : [],
        ];
    }

    /**
     * Returns the taxes rates for the given taxable and context.
     *
     * @param TaxableInterface $taxable
     * @param ContextInterface $context
     *
     * @return float[]
     */
    public function getTaxesRates(TaxableInterface $taxable, ContextInterface $context): array
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
    protected function getOffers(Model\ProductInterface $product, ContextInterface $context): array
    {
        $offers = $this->offerRepository->findByProductAndContext($product, $context);

        if (!Types::isBundleType($product)) {
            return $offers;
        }

        $children = [];

        $this->listChildren($children, $product, $context, $offers);

        $total = $hidden = 0;

        // Gather min quantities
        $quantities = array_map(function ($o) {
            return $o['min_qty'];
        }, $offers);
        foreach ($children as &$child) {
            $total += $child['price'];

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
     * @param array                  $offers The parent offers
     * @param int                    $qty
     *
     * @return bool
     */
    protected function listChildren(
        array &$list,
        Model\ProductInterface $bundle,
        ContextInterface $context,
        array $offers,
        $qty = 1
    ): bool {
        Types::assertBundle($bundle);

        $visible = false;

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $product = $choice->getProduct();

            if (Types::isBundleType($product)) {
                $offers = $this->mergeOffers(
                    $offers,
                    $this->offerRepository->findByProductAndContext($product, $context)
                );

                $visible |= $this->listChildren($list, $product, $context, $offers, $qty * $choice->getMinQuantity());
            } else {
                $child = [
                    'price'  => $product->getNetPrice() * $qty * $choice->getMinQuantity(),
                    'offers' => [],
                ];

                if ($product->isVisible() && !$choice->isHidden() && $product->hasRequiredOptionGroup($choice->getExcludedOptionGroups())) {
                    $visible = true;
                    $child['offers'] = $this->mergeOffers(
                        $offers,
                        $this->offerRepository->findByProductAndContext($product, $context)
                    );
                } else {
                    $child['offers'] = $offers;
                }

                $list[] = $child;
            }
        }

        return $visible;
    }

    /**
     * Merges two offers lists.
     *
     * @param array $a
     * @param array $b
     *
     * @return array
     */
    protected function mergeOffers(array $a, array $b): array
    {
        $offers = [];

        $quantities = array_unique(array_map(function ($o) {
            return $o['min_qty'];
        }, $a, $b));

        sort($quantities);

        foreach ($quantities as $quantity) {
            $percent = 0;

            foreach ($a as $o) {
                if ($o['min_qty'] <= $quantity && $o['percent'] > $percent) {
                    $percent = $o['percent'];
                    break;
                }
            }

            foreach ($b as $o) {
                if ($o['min_qty'] <= $quantity && $o['percent'] > $percent) {
                    $percent = $o['percent'];
                    break;
                }
            }

            if (0 < $percent) {
                $offers[] = [
                    'percent' => $percent,
                    'min_qty' => $quantity,
                ];
            }
        }

        return $offers;
    }
}
