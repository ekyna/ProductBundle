<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
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
     * @param PriceCalculator         $priceCalculator
     * @param LocaleProviderInterface $localeProvider
     * @param FormatterFactory        $formatterFactory
     * @param TranslatorInterface     $translator
     * @param EngineInterface         $templating
     * @param array                   $options
     */
    public function __construct(
        PriceCalculator $priceCalculator,
        LocaleProviderInterface $localeProvider,
        FormatterFactory $formatterFactory,
        TranslatorInterface $translator,
        EngineInterface $templating,
        array $options
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->localeProvider = $localeProvider;
        $this->formatterFactory = $formatterFactory;
        $this->translator = $translator;
        $this->templating = $templating;

        $this->options = array_replace([
            'final_price_format'    => '%s&nbsp;<sup>%s</sup>',
            'original_price_format' => '<del>%s</del>&nbsp;%s',
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
     * @return string
     */
    public function getProductPrice(Model\ProductInterface $product, ContextInterface $context = null, $discount = true)
    {
        // TODO user locale and currency (in context provider)

        $price = $this->priceCalculator->getPrice($product, $context);

        $formatter = $this->getFormatter();
        if ($formatter->getCurrency() !== $price->getCurrency()) {
            $formatter = $this->formatterFactory->create(
                $this->localeProvider->getCurrentLocale(),
                $price->getCurrency()
            );
        }

        $current = sprintf(
            $this->options['final_price_format'],
            $formatter->currency($price->getTotal($discount), $price->getCurrency()),
            $this->translator->trans(VatDisplayModes::getLabel($price->getMode()))
        );

        $from = false;
        if (!in_array($product->getType(), [Model\ProductTypes::TYPE_SIMPLE, Model\ProductTypes::TYPE_VARIANT], true)) {
            $from = true;
        } else {
            foreach ($product->getOptionGroups() as $optionGroup) {
                if ($optionGroup->isRequired()) {
                    $from = true;
                }
            }
        }

        $prefix = $this->options['price_with_from'] && $from
            ? $this->translator->trans('ekyna_commerce.subject.price_from') . ' '
            : '';

        if ($discount && $price->hasDiscounts()) {
            $previous = $formatter->currency($price->getTotal(false), $price->getCurrency());

            return $prefix . sprintf($this->options['original_price_format'], $previous, $current);
        }

        return $prefix . $current;
    }

    /**
     * Returns the configurable product total price.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return float|int
     */
    public function getBundlePrice(Model\ProductInterface $bundle)
    {
        return $this->priceCalculator->calculateBundleMinPrice($bundle);
    }

    /**
     * Returns the bundle product total price.
     *
     * @param Model\ProductInterface $configurable
     *
     * @return float|int
     */
    public function getConfigurablePrice(Model\ProductInterface $configurable)
    {
        return $this->priceCalculator->calculateConfigurableMinPrice($configurable);
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
        $config = $this->priceCalculator->getPricingGrid($product, $context);

        if (empty($config)) {
            return '';
        }

        // TODO packaging format

        $offers = [];
        $previousMin = null;
        foreach ($config['offers'] as $offer) {
            $offers[] = [
                'min'     => $offer['quantity'],
                'max'     => $previousMin,
                'percent' => $offer['percent'] / 100,
                'price'   => $offer['price'],
            ];
            $previousMin = $offer['quantity'] - 1;
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
