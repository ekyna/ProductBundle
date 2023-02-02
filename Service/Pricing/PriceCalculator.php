<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes as Types;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\PriceRepositoryInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;

use function array_merge;
use function array_unique;

/**
 * Class PriceCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceCalculator
{
    private PriceRepositoryInterface   $priceRepository;
    private OfferRepositoryInterface   $offerRepository;
    private TaxResolverInterface       $taxResolver;
    private CurrencyConverterInterface $currencyConverter;
    private string                     $defaultCurrency;

    public function __construct(
        PriceRepositoryInterface   $priceRepository,
        OfferRepositoryInterface   $offerRepository,
        TaxResolverInterface       $taxResolver,
        CurrencyConverterInterface $currencyConverter,
        string                     $defaultCurrency
    ) {
        $this->priceRepository = $priceRepository;
        $this->offerRepository = $offerRepository;
        $this->taxResolver = $taxResolver;
        $this->currencyConverter = $currencyConverter;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * Returns the product price for one quantity.
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
                'ends_at'        => null,
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
     * @param array<Decimal> $taxes
     */
    private function addTaxes(Decimal $base, array $taxes, string $currency): Decimal
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
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     */
    public function calculateMinPrice(Model\ProductInterface $product, array|bool $exclude = []): Decimal
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
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     */
    protected function calculateMinOptionsPrice(Model\ProductInterface $product, array|bool $exclude = []): Decimal
    {
        if (true === $exclude) {
            return new Decimal(0);
        }

        $price = new Decimal(0);

        $optionGroups = $product->resolveOptionGroups($exclude);

        // For each option groups
        foreach ($optionGroups as $optionGroup) {
            // Skip non required option group
            if (!$optionGroup->isRequired()) {
                continue;
            }

            // Get option with the lowest price
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
     */
    public function calculateComponentsPrice(Model\ProductInterface $product): Decimal
    {
        $price = new Decimal(0);

        $components = Model\ProductTypes::isVariantType($product)
            ? $product->getParent()->getComponents()
            : $product->getComponents();

        foreach ($components as $component) {
            if (is_null($p = $component->getNetPrice())) {
                $p = $component->getChild()->getNetPrice();
            }

            $price += $p->mul($component->getQuantity());
        }

        return $price;
    }

    /**
     * Calculates the (simple or variant) product min price.
     *
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     */
    public function calculateProductMinPrice(
        Model\ProductInterface $product,
        array|bool             $exclude = [],
        Decimal                $price = null
    ): Decimal {
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
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     */
    public function calculateVariableMinPrice(
        Model\ProductInterface $variable,
        array|bool             $exclude = [],
        Decimal                $price = null
    ): Decimal {
        Types::assertVariable($variable);

        if ($price) {
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

        if (null === $price) {
            $price = $lowestVariant ?: new Decimal(0);
        }

        return $price;
    }

    /**
     * Calculates the bundle product min price.
     *
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @todo The product (bundle) min price should be processed and persisted during update (flush)
     */
    public function calculateBundleMinPrice(
        Model\ProductInterface $bundle,
        array|bool             $exclude = [],
        Decimal                $price = null
    ): Decimal {
        Types::assertBundle($bundle);

        if (null === $price) {
            $price = new Decimal(0);
            foreach ($bundle->getBundleSlots() as $slot) {
                /** @var BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $childProduct = $choice->getProduct();
                $choicePrice = $choice->getNetPrice();

                if (true === $exclude) {
                    $choiceExclude = true;
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
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     *
     * @todo The product (configurable) min price should be processed and persisted during update (flush)
     */
    public function calculateConfigurableMinPrice(
        Model\ProductInterface $configurable,
        array|bool             $exclude = []
    ): Decimal {
        Types::assertConfigurable($configurable);

        $price = new Decimal(0);

        // For each bundle slots
        foreach ($configurable->getBundleSlots() as $slot) {
            // Skip non required slots
            if (!$slot->isRequired()) {
                continue;
            }

            // Get slot choice with the lowest price.
            $lowestPrice = null;
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

        return $price;
    }

    /**
     * Returns the pricing data for the given product and context.
     */
    public function buildProductPricing(
        Model\ProductInterface $product,
        ContextInterface       $context,
        bool                   $withOffers = true,
        bool                   $withTaxes = true
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
            'net_price' => $amount,
            'offers'    => null,
            'taxes'     => null,
        ];

        // Offers rules
        if ($withOffers) {
            $offers = $this->getOffers($product, $context);
            foreach ($offers as &$offer) {
                unset($offer['net_price']);
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
            $offer['net_price'] = $price->mul(1 - $offer['percent'] / 100)->round(5);
        }

        return [
            'currency' => $currency,
            'offers'   => $offers,
        ];
    }

    /**
     * Returns the pricing data for the given option and context.
     */
    public function buildOptionPricing(
        Model\OptionInterface $option,
        ContextInterface      $context,
        bool                  $withOffers = true,
        bool                  $withTaxes = true
    ): array {
        if ($product = $option->getProduct()) {
            $pricing = $this->buildProductPricing($product, $context, $withOffers, $withTaxes);

            // Option's net price override
            if ($price = $option->getNetPrice()) {
                $pricing['net_price'] = $this
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
                $option->getNetPrice(),
                $this->defaultCurrency,
                $context->getCurrency()->getCode(),
                $context->getDate()
            );

        return [
            'net_price' => $price,
            'offers'    => [], // Prevent inheritance
            'taxes'     => $withTaxes ? $this->getTaxesRates($option, $context) : [],
        ];
    }

    /**
     * Returns the taxes rates for the given taxable and context.
     *
     * @return array<Decimal>
     */
    public function getTaxesRates(TaxableInterface $taxable, ContextInterface $context): array
    {
        $taxes = [];

        $config = $this->taxResolver->resolveTaxes($taxable, $context);
        foreach ($config as $tax) {
            $taxes[] = $tax->getRate();
        }

        return $taxes;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    /**
     * Returns the product offers.
     */
    protected function getOffers(Model\ProductInterface $product, ContextInterface $context): array
    {
        $offers = $this->offerRepository->findByProductAndContext($product, $context);

        if (!Types::isBundleType($product)) {
            return $offers;
        }

        $children = [];

        $this->listChildren($children, $product, $context, $offers);

        $total = new Decimal(0);

        // Gather min quantities
        $quantities = array_map(function ($o) {
            return $o['min_qty']->toFixed(5);
        }, $offers);
        foreach ($children as $child) {
            $total += $child['net_price'];

            foreach ($child['offers'] as $offer) {
                if (!in_array($qty = $offer['min_qty']->toFixed(5), $quantities, true)) {
                    $quantities[] = $qty;
                }
            }
        }
        unset($child);

        sort($quantities);

        $mergedOffers = [];
        foreach ($quantities as $quantity) {
            $discount = new Decimal(0);

            foreach ($children as $child) {
                foreach ($child['offers'] as $offer) {
                    if ($offer['min_qty'] <= $quantity) {
                        $discount += $child['net_price'] * $offer['percent'] / 100;
                        continue 2;
                    }
                }
            }

            $mergedOffers[] = [
                'min_qty' => $quantity,
                'percent' => $discount->div($total)->mul(100)->round(5),
            ];
        }

        return array_reverse($mergedOffers);
    }

    /**
     * List bundle children.
     */
    protected function listChildren(
        array                  &$list,
        Model\ProductInterface $bundle,
        ContextInterface       $context,
        array                  $parentOffers,
        Decimal                $qty = null
    ): bool {
        Types::assertBundle($bundle);

        $qty = $qty ?: new Decimal(1);

        $visible = false;

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $product = $choice->getProduct();

            if (Types::isBundleType($product)) {
                $parentOffers = $this->mergeOffers(
                    $parentOffers,
                    $this->offerRepository->findByProductAndContext($product, $context)
                );

                $visible = $this
                    ->listChildren($list, $product, $context, $parentOffers, $qty->mul($choice->getMinQuantity()))
                    || $visible;
            } else {
                $child = [
                    'net_price' => $product->getNetPrice()->mul($qty)->mul($choice->getMinQuantity()),
                ];

                if (
                    $product->isVisible()
                    && !$choice->isHidden()
                    && $product->hasRequiredOptionGroup($choice->getExcludedOptionGroups())
                ) {
                    $visible = true;
                    $child['offers'] = $this->mergeOffers(
                        $parentOffers,
                        $this->offerRepository->findByProductAndContext($product, $context)
                    );
                } else {
                    $child['offers'] = $parentOffers;
                }

                $list[] = $child;
            }
        }

        return $visible;
    }

    /**
     * Merges two offers lists.
     */
    protected function mergeOffers(array $a, array $b): array
    {
        $offers = [];

        $quantities = array_unique(array_map(fn(array $o) => $o['min_qty'], array_merge($a, $b)));

        sort($quantities);

        foreach ($quantities as $quantity) {
            $percent = new Decimal(0);

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
