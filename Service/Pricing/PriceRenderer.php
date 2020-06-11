<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class PriceRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceRenderer
{
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PurchaseCostCalculator
     */
    private $purchaseCostCalculator;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider; // TODO remove as not used.

    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var FormatterFactory
     */
    private $formatterFactory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $options;


    /**
     * Constructor.
     *
     * @param PriceCalculator          $priceCalculator
     * @param PurchaseCostCalculator   $purchaseCostCalculator
     * @param LocaleProviderInterface  $localeProvider
     * @param ContextProviderInterface $contextProvider
     * @param FormatterFactory         $formatterFactory
     * @param TranslatorInterface      $translator
     * @param EngineInterface          $templating
     * @param array                    $options
     */
    public function __construct(
        PriceCalculator $priceCalculator,
        PurchaseCostCalculator $purchaseCostCalculator,
        LocaleProviderInterface $localeProvider,
        ContextProviderInterface $contextProvider,
        FormatterFactory $formatterFactory,
        TranslatorInterface $translator,
        EngineInterface $templating,
        array $options
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->purchaseCostCalculator = $purchaseCostCalculator;
        $this->localeProvider = $localeProvider;
        $this->contextProvider = $contextProvider;
        $this->formatterFactory = $formatterFactory;
        $this->translator = $translator;
        $this->templating = $templating;

        $this->options = array_replace([
            'final_price_format'    => '<strong>{amount}</strong>&nbsp;<sup>{mode}</sup>',
            'original_price_format' => '<del>{amount}</del>&nbsp;',
            'price_with_from'       => false,
        ], $options);
    }

    /**
     * Renders the product price.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface       $context
     * @param bool                   $discount
     * @param bool                   $extended
     *
     * @return Model\PriceDisplay
     */
    public function getProductPrice(
        Model\ProductInterface $product,
        ContextInterface $context = null,
        bool $discount = true,
        bool $extended = false
    ): Model\PriceDisplay {
        if (null === $context) {
            $context = $this->contextProvider->getContext();
        }

        $price = $this->priceCalculator->getPrice($product, $context);

        $currency = $context->getCurrency()->getCode();
        $mode = $context->getVatDisplayMode();

        $formatter = $this->formatterFactory->create($context->getLocale(), $currency);

        // From
        $fromLabel = ($this->options['price_with_from'] && $price['starting_from'])
            ? $this->translator->trans('ekyna_product.price.display.starting_from') . '&nbsp;'
            : '';

        $mode = $this->translator->trans(VatDisplayModes::getLabel($mode));

        // Final
        $final = (!$discount && 0 < $price['original_price'])
            ? $price['original_price']
            : $price['sell_price'];

        $finalLabel = strtr($this->options['final_price_format'], [
            '{amount}' => $formatter->currency($final, $currency),
            '{mode}'   => $mode,
        ]);

        // Original
        $originalLabel = '';
        if ($discount && 0 < $price['original_price']) {
            $originalLabel = strtr($this->options['original_price_format'], [
                '{amount}' => $formatter->currency($price['original_price'], $currency),
                '{mode}'   => $mode,
            ]);
        }

        $endsAt = $price['ends_at'] ? $formatter->date(new \DateTime($price['ends_at'])) : '';

        // Result
        $display = new Model\PriceDisplay($final, $fromLabel, $originalLabel, $finalLabel, $endsAt);

        if ($extended && $endsAt) {
            $display->addMention($this->translator->trans('ekyna_product.price.display.valid_until', [
                '%date%' => $endsAt,
            ]));
        }

        // Special offer
        if (isset($price['details'][Offer::TYPE_SPECIAL]) && 0 < $price['details'][Offer::TYPE_SPECIAL]) {
            $display->setSpecialPercent($percent = $formatter->percent($price['details'][Offer::TYPE_SPECIAL]));

            if ($extended) {
                $display->setSpecialLabel($this->translator->trans('ekyna_product.price.display.special_offer', [
                    '%percent%' => $percent,
                ]));
            }
        }

        // Pricing
        if (isset($price['details'][Offer::TYPE_PRICING]) && 0 < $price['details'][Offer::TYPE_PRICING]) {
            $display->setPricingPercent($percent = $formatter->percent($price['details'][Offer::TYPE_PRICING]));

            if ($extended) {
                $display->setPricingLabel($this->translator->trans('ekyna_product.price.display.pricing', [
                    '%percent%' => $percent,
                ]));
            }
        }

        if ($extended && ($display->getSpecialPercent() || $display->getPricingPercent())) {
            $display->addMention($this->translator->trans('ekyna_product.price.display.while_stock'));
        }

        return $display;
    }

    /**
     * Returns the configurable product total price.
     *
     * @param Model\ProductInterface $bundle
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float
     */
    public function getBundlePrice(Model\ProductInterface $bundle, $withOptions = true): float
    {
        return $this->priceCalculator->calculateBundleMinPrice($bundle, !$withOptions);
    }

    /**
     * Returns the bundle product total price.
     *
     * @param Model\ProductInterface $configurable
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float
     */
    public function getConfigurablePrice(Model\ProductInterface $configurable, $withOptions = true): float
    {
        return $this->priceCalculator->calculateConfigurableMinPrice($configurable, !$withOptions);
    }

    /**
     * Returns the product's components total price.
     *
     * @param Model\ProductInterface $product
     *
     * @return float
     */
    public function getComponentsPrice(Model\ProductInterface $product): float
    {
        return $this->priceCalculator->calculateComponentsPrice($product);
    }

    /**
     * Returns the product purchase cost.
     *
     * @param Model\ProductInterface $product
     * @param bool                   $withOptions Whether to add options min cost.
     *
     * @return float|int
     */
    public function getPurchaseCost(Model\ProductInterface $product, $withOptions = true): float
    {
        return $this->purchaseCostCalculator->calculateMinPurchaseCost($product, $withOptions);
    }

    /**
     * Renders the product pricing grid.
     *
     * @param Model\ProductInterface $product
     * @param ContextInterface|null  $context
     * @param string                 $class
     *
     * @return string
     */
    public function renderPricingGrid(
        Model\ProductInterface $product,
        ContextInterface $context = null,
        $class = 'product-pricing-grid'
    ) {
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
                'min'     => $offer['min_qty'],
                'max'     => $previousMin,
                'percent' => round($offer['percent'], 2),
                'price'   => $offer['price'],
            ];
            $previousMin = $offer['min_qty'] - 1;
        }

        $config['offers'] = array_reverse($offers);

        return $this->templating->render('@EkynaProduct/Pricing/grid.html.twig', [
            'pricing' => $config,
            'class'   => $class,
        ]);
    }
}
