<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Util\Formatter;
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
     * @var LocaleProviderInterface
     */
    private $localeProvider;

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
     * @var Formatter
     */
    private $formatter;

    /**
     * @var array
     */
    private $options;


    /**
     * Constructor.
     *
     * @param PriceCalculator          $priceCalculator
     * @param LocaleProviderInterface  $localeProvider
     * @param ContextProviderInterface $contextProvider
     * @param FormatterFactory         $formatterFactory
     * @param TranslatorInterface      $translator
     * @param EngineInterface          $templating
     * @param array                    $options
     */
    public function __construct(
        PriceCalculator $priceCalculator,
        LocaleProviderInterface $localeProvider,
        ContextProviderInterface $contextProvider,
        FormatterFactory $formatterFactory,
        TranslatorInterface $translator,
        EngineInterface $templating,
        array $options
    ) {
        $this->priceCalculator = $priceCalculator;
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
     *
     * @return Model\PriceDisplay
     */
    public function getProductPrice(Model\ProductInterface $product, ContextInterface $context = null, $discount = true)
    {
        // Do not display price for configurable products
        /*if (Model\ProductTypes::isConfigurableType($product)) {
            return new Model\PriceDisplay(0, '', '', 'NC'); // TODO translation
        }*/

        // TODO user locale and currency (in context provider)

        if (null === $context) {
            $context = $this->contextProvider->getContext();
        }

        $price = $this->priceCalculator->getPrice($product, $context);

        $currency = $context->getCurrency()->getCode();
        $mode = $context->getVatDisplayMode();

        $formatter = $this->getFormatter();
        if ($formatter->getCurrency() !== $currency) {
            $formatter = $this->formatterFactory->create($this->localeProvider->getCurrentLocale(), $currency);
        }

        // From
        $fromLabel = $this->options['price_with_from'] && $price['starting_from']
            ? $this->translator->trans('ekyna_commerce.subject.price_from') . '&nbsp;'
            : '';

        $mode = $this->translator->trans(VatDisplayModes::getLabel($mode));

        // Final
        $final = (!$discount && 0 < $price['original_price'])
            ? $price['original_price']
            : $price['sell_price'];

        $finalLabel = strtr($this->options['final_price_format'], [
            '{amount}' => $formatter->currency($final, $currency),
            '{mode}' => $mode,
        ]);

        // Original
        $originalLabel = '';
        if ($discount && 0 < $price['original_price']) {
            $originalLabel = strtr($this->options['original_price_format'], [
                '{amount}' => $formatter->currency($price['original_price'], $currency),
                '{mode}' => $mode,
            ]);
        }

        // Result
        $display = new Model\PriceDisplay($final, $fromLabel, $originalLabel, $finalLabel);

        if (isset($price['details'][Offer::TYPE_SPECIAL]) && 0 < $price['details'][Offer::TYPE_SPECIAL]) {
            $display->setSpecialPercent($formatter->percent($price['details'][Offer::TYPE_SPECIAL]));
        }
        if (isset($price['details'][Offer::TYPE_PRICING]) && 0 < $price['details'][Offer::TYPE_PRICING]) {
            $display->setPricingPercent($formatter->percent($price['details'][Offer::TYPE_PRICING]));
        }

        return $display;
    }

    /**
     * Returns the configurable product total price.
     *
     * @param Model\ProductInterface $bundle
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float|int
     */
    public function getBundlePrice(Model\ProductInterface $bundle, $withOptions = true)
    {
        return $this->priceCalculator->calculateBundleMinPrice($bundle, $withOptions);
    }

    /**
     * Returns the bundle product total price.
     *
     * @param Model\ProductInterface $configurable
     * @param bool                   $withOptions Whether to add options min price.
     *
     * @return float|int
     */
    public function getConfigurablePrice(Model\ProductInterface $configurable, $withOptions = true)
    {
        return $this->priceCalculator->calculateConfigurableMinPrice($configurable, $withOptions);
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
            return '';
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

    /**
     * Returns the formatter.
     *
     * @return Formatter
     */
    protected function getFormatter()
    {
        if (null !== $this->formatter) {
            return $this->formatter;
        }

        return $this->formatter = $this->formatterFactory->create(
            $this->localeProvider->getCurrentLocale()
        // TODO currency
        );
    }
}
