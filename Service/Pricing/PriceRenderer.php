<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use DateTime;
use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\OfferInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function array_replace;

/**
 * Class PriceRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceRenderer
{
    private PriceCalculator          $priceCalculator;
    private PurchaseCostCalculator   $purchaseCostCalculator;
    private ContextProviderInterface $contextProvider;
    private FormatterFactory         $formatterFactory;
    private TranslatorInterface      $translator;
    private Environment              $twig;
    private array                    $options;

    public function __construct(
        PriceCalculator          $priceCalculator,
        PurchaseCostCalculator   $purchaseCostCalculator,
        ContextProviderInterface $contextProvider,
        FormatterFactory         $formatterFactory,
        TranslatorInterface      $translator,
        Environment              $twig,
        array                    $options
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->purchaseCostCalculator = $purchaseCostCalculator;
        $this->contextProvider = $contextProvider;
        $this->formatterFactory = $formatterFactory;
        $this->translator = $translator;
        $this->twig = $twig;

        $this->options = array_replace([
            'final_price_format'    => '<strong>{amount}</strong>&nbsp;<sup>{mode}</sup>',
            'original_price_format' => '<del>{amount}</del>&nbsp;',
            'price_with_from'       => false,
        ], $options);
    }

    /**
     * Renders the product price.
     */
    public function getProductPrice(Model\ProductInterface $product, array $options = []): Model\PriceDisplay
    {
        $options = array_replace([
            'context'  => null,
            'discount' => true,
            'extended' => false,
        ], $options);

        if (null === $context = $options['context']) {
            $context = $this->contextProvider->getContext();
        }

        $price = $this->priceCalculator->getPrice($product, $context);

        $currency = $context->getCurrency()->getCode();
        $mode = $context->getVatDisplayMode();

        $formatter = $this->formatterFactory->create($context->getLocale(), $currency);

        // From
        $fromLabel = ($this->options['price_with_from'] && $price['starting_from'])
            ? $this->translator->trans('price.display.starting_from', [], 'EkynaProduct') . '&nbsp;'
            : '';

        $mode = VatDisplayModes::getLabel($mode)->trans($this->translator);

        // Final
        $final = (!$options['discount'] && 0 < $price['original_price'])
            ? $price['original_price']
            : $price['sell_price'];

        $finalLabel = strtr($this->options['final_price_format'], [
            '{amount}' => $formatter->currency($final, $currency),
            '{mode}'   => $mode,
        ]);

        // Original
        $originalLabel = '';
        if ($options['discount'] && 0 < $price['original_price']) {
            $originalLabel = strtr($this->options['original_price_format'], [
                '{amount}' => $formatter->currency($price['original_price'], $currency),
                '{mode}'   => $mode,
            ]);
        }

        $endsAt = $price['ends_at'] ? $formatter->date(new DateTime($price['ends_at'])) : '';

        // Result
        $display = new Model\PriceDisplay($final, $fromLabel, $originalLabel, $finalLabel, $endsAt);

        if ($options['extended'] && $endsAt) {
            $display->addMention($this->translator->trans('price.display.valid_until', [
                '%date%' => $endsAt,
            ], 'EkynaProduct'));
        }

        // Special offer
        if (isset($price['details'][OfferInterface::TYPE_SPECIAL])
            && 0 < $price['details'][OfferInterface::TYPE_SPECIAL]) {
            $display->setSpecialPercent($percent = $formatter->percent($price['details'][OfferInterface::TYPE_SPECIAL]));

            if ($options['extended']) {
                $display->setSpecialLabel($this->translator->trans('price.display.special_offer', [
                    '%percent%' => $percent,
                ], 'EkynaProduct'));
            }
        }

        // Pricing
        if (isset($price['details'][OfferInterface::TYPE_PRICING])
            && 0 < $price['details'][OfferInterface::TYPE_PRICING]) {
            $display->setPricingPercent($percent = $formatter->percent($price['details'][OfferInterface::TYPE_PRICING]));

            if ($options['extended']) {
                $display->setPricingLabel($this->translator->trans('price.display.pricing', [
                    '%percent%' => $percent,
                ], 'EkynaProduct'));
            }
        }

        if ($options['extended'] && ($display->getSpecialPercent() || $display->getPricingPercent())) {
            $display->addMention($this->translator->trans('price.display.while_stock', [], 'EkynaProduct'));
        }

        return $display;
    }

    /**
     * Returns the configurable product total price.
     *
     * @param Model\ProductInterface $bundle
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return Decimal
     */
    public function getBundlePrice(Model\ProductInterface $bundle, bool $withOptions = true): Decimal
    {
        return $this->priceCalculator->calculateBundleMinPrice($bundle, !$withOptions);
    }

    /**
     * Returns the bundle product total price.
     *
     * @param Model\ProductInterface $configurable
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return Decimal
     */
    public function getConfigurablePrice(Model\ProductInterface $configurable, bool $withOptions = true): Decimal
    {
        return $this->priceCalculator->calculateConfigurableMinPrice($configurable, !$withOptions);
    }

    /**
     * Returns the product's components total price.
     *
     * @param Model\ProductInterface $product
     *
     * @return Decimal
     */
    public function getComponentsPrice(Model\ProductInterface $product): Decimal
    {
        return $this->priceCalculator->calculateComponentsPrice($product);
    }

    /**
     * Returns the product purchase cost.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $withOptions Whether to add options min cost.
     * @param bool                   $shipping    Whether to include shipping cost.
     *
     * @return Decimal
     */
    public function getPurchaseCost(
        Model\ProductInterface $product,
        bool                   $withOptions = true,
        bool                   $shipping = false
    ): Decimal {
        return $this->purchaseCostCalculator->calculateMinPurchaseCost($product, $withOptions, $shipping);
    }

    /**
     * Renders the product pricing grid.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface|null  $context
     * @param string                 $class
     *
     * @return string|null
     */
    public function renderPricingGrid(
        Model\ProductInterface $product,
        ContextInterface       $context = null,
        string                 $class = 'product-pricing-grid'
    ): ?string {
        if (null === $context) {
            $context = $this->contextProvider->getContext();
        }

        $config = $this->priceCalculator->getPricingGrid($product, $context);

        if (empty($config)) {
            return null;
        }

        // TODO packaging format

        $offers = [];
        $previousMin = null;
        foreach ($config['offers'] as $offer) {
            $offers[] = [
                'min'       => $offer['min_qty'],
                'max'       => $previousMin,
                'percent'   => round($offer['percent'], 2),
                'net_price' => $offer['net_price'],
            ];
            $previousMin = $offer['min_qty'] - 1;
        }

        $config['offers'] = array_reverse($offers);

        return $this->twig->render('@EkynaProduct/Pricing/grid.html.twig', [
            'pricing' => $config,
            'class'   => $class,
        ]);
    }
}
