<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Pricing\Model\Price;
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
     * @var PriceResolver
     */
    private $priceResolver;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param PriceResolver              $priceResolver
     * @param TaxResolverInterface       $taxResolver
     * @param CurrencyConverterInterface $currencyConverter
     * @param ContextProviderInterface   $contextProvider
     * @param string                     $defaultCurrency
     */
    public function __construct(
        PriceResolver $priceResolver,
        TaxResolverInterface $taxResolver,
        CurrencyConverterInterface $currencyConverter,
        ContextProviderInterface $contextProvider,
        $defaultCurrency
    ) {
        $this->priceResolver = $priceResolver;
        $this->taxResolver = $taxResolver;
        $this->currencyConverter = $currencyConverter;
        $this->contextProvider = $contextProvider;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * Returns the product price for one quantity.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     *
     * @return Price
     */
    public function getPrice(Model\ProductInterface $product, ContextInterface $context = null)
    {
        if (null === $context) {
            $context = $this->contextProvider->getContext();
        }

        $amount = $product->getNetPrice();

        // Vat display mode and Currency
        $mode = $context->getVatDisplayMode();
        $currency = $context->getCurrency()->getCode();

        // Currency conversion
        if ($currency !== $this->defaultCurrency) {
            $amount = $this->currencyConverter->convert($amount, $this->defaultCurrency, $currency);
        }

        // Price
        $price = new Price($amount, $currency, $mode);

        // Discount adjustment
        if (null !== $discount = $this->priceResolver->resolve($product, $context, 1)) {
            $price->addDiscount($discount);
        }

        // Taxation adjustments
        if ($mode === VatDisplayModes::MODE_ATI) {
            $taxes = $this->taxResolver->resolveTaxes($product, $context->getDeliveryCountry());
            foreach ($taxes as $tax) {
                $price->addTax($tax);
            }
        }

        return $price;
    }

    /**
     * Calculates the product (bundle) total price.
     *
     * @param Model\ProductInterface $product
     *
     * @return float|int
     *
     * @todo The product (bundle) min price should be processed and persisted during update (flush)
     */
    public function calculateBundleTotalPrice(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertBundle($product);

        $total = 0;

        foreach ($product->getBundleSlots() as $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();

            // TODO Use packaging format

            $total += $choice->getProduct()->getNetPrice() * $choice->getMinQuantity();

            // TODO required options ?

            // TODO Recurse if parent type
        }

        return $total;
    }

    /**
     * Calculates the product (configurable) total.
     *
     * @param Model\ProductInterface $product
     *
     * @return float|int
     *
     * @todo The product (configurable) min price should be processed and persisted during update (flush)
     */
    public function calculateConfigurableTotalPrice(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertConfigurable($product);

        $total = 0;

        foreach ($product->getBundleSlots() as $slot) {
            $lowerPrice = null;

            foreach ($slot->getChoices() as $choice) {
                $childProduct = $choice->getProduct();

                // TODO Use packaging format

                $childPrice = $childProduct->getNetPrice() * $choice->getMinQuantity();

                // TODO required options ?

                // TODO Recurse if parent type

                if (null === $lowerPrice || $childPrice < $lowerPrice) {
                    $lowerPrice = $childPrice;
                }
            }

            $total += $lowerPrice;
        }

        return $total;
    }

    /**
     * Returns the pricing data for the given sale item.
     *
     * @param SaleItemInterface $item
     * @param bool              $fallback Whether to fallback to the logged in customer
     *
     * @return array The rules (array with quantities as keys and percentages as values)
     */
    /*public function getSaleItemPricingData(SaleItemInterface $item, $fallback = true)
    {
        if (!$this->productProvider->supportsRelative($item)) {
            return [];
        }

        if (null === $product = $this->productProvider->resolve($item)) {
            return [];
        }

        $context = $this->contextProvider->getContext($item->getSale(), $fallback);

        return $this->buildProductPricing($product, $context);
    }*/

    /**
     * Returns the pricing data for the given product and context.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     * @param bool                   $discounts
     * @param bool                   $taxes
     *
     * @return array
     */
    public function buildProductPricing(
        Model\ProductInterface $product,
        ContextInterface $context,
        bool $discounts = true,
        bool $taxes = true
    ) {
        // Net price
        $amount = $product->getNetPrice();

        // Currency
        $currency = $context->getCurrency()->getCode();
        if ($currency !== $this->defaultCurrency) {
            // Currency conversion
            $amount = $this->currencyConverter->convert($amount, $this->defaultCurrency, $currency);
        }

        $pricing = [
            'price'     => (float)$amount,
            'discounts' => null,
            'taxes'     => null,
        ];

        // Discount rules
        if ($discounts) {
            $config = $this->priceResolver->findPricing($product, $context);
            $pricing['discounts'] = isset($config['rules']) ? $config['rules'] : []; // TODO unset rules id
        }

        /** @see \Ekyna\Component\Commerce\Common\Resolver\DiscountResolver::resolveSaleItem() */
        // Don't apply discounts/taxes to private items (they will inherit from parents)
        /*if ($item->isPrivate()) {
            return $data;
        }*/

        // Taxes
        if ($taxes) {
            $pricing['taxes'] = $this->getTaxesRates($product, $context);
        }

        // Don't apply discount to parent with only public children
        /*if ($item->isCompound() && !$item->hasPrivateChildren()) {
            return $data;
        }*/

        return $pricing;
    }

    /**
     * Returns the pricing data for the given option and context.
     *
     * @param Model\OptionInterface $option
     * @param ContextInterface      $context
     * @param bool                  $discounts
     * @param bool                  $taxes
     *
     * @return array
     */
    public function buildOptionPricing(
        Model\OptionInterface $option,
        ContextInterface $context,
        bool $discounts = true,
        bool $taxes = true
    ) {
        // Currency
        $currency = $context->getCurrency()->getCode();

        if (null !== $product = $option->getProduct()) {
            $pricing = $this->buildProductPricing($product, $context, $discounts, $taxes);

            // Option's net price override
            if (null !== $price = $option->getNetPrice()) {
                if ($currency !== $this->defaultCurrency) {
                    // Currency conversion
                    $price = $this->currencyConverter->convert($price, $this->defaultCurrency, $currency);
                }
                $pricing['price'] = $price;
            }
            return $pricing;
        }

        return [
            'price'     => (float)$option->getNetPrice(),
            'discounts' => [], // Prevent inheritance
            'taxes'     => $this->getTaxesRates($option, $context),
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
    public function getTaxesRates(TaxableInterface $taxable, ContextInterface $context)
    {
        $taxes = [];

        $config = $this->taxResolver->resolveTaxes($taxable, $context->getDeliveryCountry());
        foreach ($config as $tax) {
            $taxes[] = (float)$tax->getRate();
        }

        return $taxes;
    }
}
