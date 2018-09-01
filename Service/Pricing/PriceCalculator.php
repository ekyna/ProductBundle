<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Converter\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
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
     * @param OfferRepositoryInterface   $offerRepository
     * @param TaxResolverInterface       $taxResolver
     * @param CurrencyConverterInterface $currencyConverter
     * @param ContextProviderInterface   $contextProvider
     * @param string                     $defaultCurrency
     */
    public function __construct(
        OfferRepositoryInterface $offerRepository,
        TaxResolverInterface $taxResolver,
        CurrencyConverterInterface $currencyConverter,
        ContextProviderInterface $contextProvider,
        $defaultCurrency
    ) {
        $this->offerRepository = $offerRepository;
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
        $offer = $this
            ->offerRepository
            ->findOneByProductAndContextAndQuantity($product, $context);

        if (!is_null($offer)) {
            $price->addDiscount(new AdjustmentData(
                AdjustmentModes::MODE_PERCENT,
                sprintf('%s %s%%', 'Reduction', $offer['percent']), // TODO designation
                // TODO translation / number_format
                $offer['percent']
            ));
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
     * Calculates the product min options price.
     *
     * @param Model\ProductInterface $product
     *
     * @return float|int
     */
    public function calculateMinOptionsPrice(Model\ProductInterface $product)
    {
        $price = 0;

        // For each option groups
        foreach ($product->getOptionGroups() as $optionGroup) {
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
     *
     * @return float|int
     */
    public function calculateProductMinPrice(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertChildType($product);

        return $product->getNetPrice() + $this->calculateMinOptionsPrice($product);
    }

    /**
     * Calculates the variable product min price.
     *
     * @param Model\ProductInterface $variable
     *
     * @return float|int
     */
    public function calculateVariableMinPrice(Model\ProductInterface $variable)
    {
        Model\ProductTypes::assertVariable($variable);

        $price = null;

        foreach ($variable->getVariants() as $variant) {
            if (!$variant->isVisible()) {
                continue;
            }

            $variantPrice = $this->calculateProductMinPrice($variant);

            if (null === $price || $variantPrice < $price) {
                $price = $variantPrice;
            }
        }

        return $price + $this->calculateMinOptionsPrice($variable);
    }

    /**
     * Calculates the bundle product min price.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return float|int
     *
     * @todo The product (bundle) min price should be processed and persisted during update (flush)
     */
    public function calculateBundleMinPrice(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        $total = 0;

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $childProduct = $choice->getProduct();

            if ($childProduct->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                $childPrice = $this->calculateBundleMinPrice($childProduct);
            } elseif ($childProduct->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                $childPrice = $this->calculateVariableMinPrice($childProduct);
            } else {
                $childPrice = $this->calculateProductMinPrice($childProduct);
            }

            // TODO Use packaging format
            $total += $childPrice * $choice->getMinQuantity();
        }

        return $total + $this->calculateMinOptionsPrice($bundle);
    }

    /**
     * Calculates the configurable product min price.
     *
     * @param Model\ProductInterface $configurable
     *
     * @return float|int
     *
     * @todo The product (configurable) min price should be processed and persisted during update (flush)
     */
    public function calculateConfigurableMinPrice(Model\ProductInterface $configurable)
    {
        Model\ProductTypes::assertConfigurable($configurable);

        $total = 0;

        // For each bundle slots
        foreach ($configurable->getBundleSlots() as $slot) {
            // Skip non required slots
            if (!$slot->isRequired()) {
                continue;
            }

            // Get slot choice with lowest price.
            $lowestChoice = null;
            // For each slot choices
            foreach ($slot->getChoices() as $choice) {
                $childProduct = $choice->getProduct();

                // TODO Recurse if parent type (?)

                if ($childProduct->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                    $childPrice = $this->calculateBundleMinPrice($childProduct);
                } elseif ($childProduct->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                    $childPrice = $this->calculateVariableMinPrice($childProduct);
                } else {
                    $childPrice = $this->calculateProductMinPrice($childProduct);
                }

                // TODO Use packaging format
                $childPrice *= $choice->getMinQuantity();

                if (null === $lowestChoice || $childPrice < $lowestChoice) {
                    $lowestChoice = $childPrice;
                }
            }

            $total += $lowestChoice;
        }

        return $total + $this->calculateMinOptionsPrice($configurable);
    }

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

        // Offers rules
        if ($discounts) {
            $offers = $this->offerRepository->findByProductAndContext($product, $context);
            foreach ($offers as &$offer) {
                unset($offer['price']);
            }
            $pricing['discounts'] = $offers;
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
     * Returns the pricing grid for the given product and context.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface|null  $context
     *
     * @return array
     */
    public function getPricingGrid(Model\ProductInterface $product, ContextInterface $context = null)
    {
        if (null === $context) {
            $context = $this->contextProvider->getContext();
        }

        $offers = $this->offerRepository->findByProductAndContext($product, $context);

        if (empty($offers)) {
            return [];
        }

        $pricing = [
            'currency' => $currency = $context->getCurrency()->getCode(),
            'offers'   => $offers,
        ];

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

        foreach ($pricing['offers'] as &$offer) {
            $offer['price'] = round($price * (1 - $offer['percent'] / 100), 5);
        }

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
