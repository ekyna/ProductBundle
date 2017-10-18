<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;

/**
 * Class PriceCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceCalculator
{
    /**
     * @var ProductProvider
     */
    private $productProvider;

    /**
     * @var PriceResolver
     */
    private $priceResolver;

    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var CartProviderInterface
     */
    private $cartProvider;

    /**
     * @var CustomerGroupInterface
     */
    private $customerGroup;

    /**
     * @var CountryInterface
     */
    private $country;

    /**
     * @var bool
     */
    private $initialized;


    /**
     * Constructor.
     *
     * @param ProductProvider           $productProvider
     * @param PriceResolver             $priceResolver
     * @param CustomerProviderInterface $customerProvider
     * @param CartProviderInterface     $cartProvider
     */
    public function __construct(
        ProductProvider $productProvider,
        PriceResolver $priceResolver,
        CustomerProviderInterface $customerProvider,
        CartProviderInterface $cartProvider
    ) {
        $this->productProvider = $productProvider;
        $this->priceResolver = $priceResolver;
        $this->customerProvider = $customerProvider;
        $this->cartProvider = $cartProvider;
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
    public function getSaleItemPricingData(SaleItemInterface $item, $fallback = true)
    {
        if (!$this->productProvider->supportsRelative($item)) {
            return [];
        }

        if (null === $product = $this->productProvider->resolve($item)) {
            return [];
        }

        // Resolves customer and country from the item's sale
        $customer = $country = $currency = null;
        if (null !== $sale = $item->getSale()) {
            // Sale currency
            $currency = $sale->getCurrency()->getCode();

            // Sale invoice address country
            if (null !== $address = $sale->getInvoiceAddress()) {
                $country = $address->getCountry();
            }

            // Sale customer
            $customer = $sale->getCustomer();
            if (!$country && $customer) {
                // Customer default invoice country
                if (null !== $address = $customer->getDefaultInvoiceAddress(true)) {
                    $country = $address->getCountry();
                }
            }
        }

        if (!$currency) {
            $currency = 'EUR'; // TODO default currency
        }

        $data = [
            'price'    => floatval($product->getNetPrice()),
            'currency' => $currency,
            'rules'    => [],
        ];

        if (!Model\ProductTypes::isBundled($product->getType())) {
            if ($customer && $country) {
                $pricing = $this->priceResolver->findPricing($product, $customer->getCustomerGroup(), $country);
                if (isset($pricing['rules'])) {
                    $data['rules'] = $pricing['rules'];
                }
            } elseif ($fallback) {
                $data['rules'] = $this->getProductPricingRules($product);
            }
        }

        return $data;
    }

    /**
     * Returns the pricing rules for the given product.
     *
     * @param Model\ProductInterface $product
     *
     * @return array The rules (array with quantities as keys and percentages as values)
     */
    public function getProductPricingRules(Model\ProductInterface $product)
    {
        $this->initialize();

        $pricing = $this->priceResolver->findPricing($product, $this->customerGroup, $this->country);
        if (isset($pricing['rules'])) {
            return $pricing['rules'];
        }

        return [];
    }

    /**
     * Initializes the calculator by resolving the customer and the country.
     */
    private function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $customer = null;
        if ($this->cartProvider->hasCart()) {
            // Cart customer group
            $cart = $this->cartProvider->getCart();
            if (null !== $customer = $cart->getCustomer()) {
                $this->customerGroup = $cart->getCustomer()->getCustomerGroup();
            }

            // Cart invoice address
            if (null !== $address = $cart->getInvoiceAddress()) {
                $this->country = $address->getCountry();
            }
        }

        // Logged in customer
        if ((!$this->customerGroup || !$this->country) && $this->customerProvider->hasCustomer()) {
            $customer = $this->customerProvider->getCustomer();
            if (!$this->customerGroup) {
                $this->customerGroup = $customer->getCustomerGroup();
            }
            if (!$this->country) {
                if (null !== $address = $customer->getDefaultInvoiceAddress(true)) {
                    $this->country = $address->getCountry();
                }
            }
        }

        $this->initialized = true;
    }
}
