<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\VatDisplayModes;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ProductExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var AttributeTypeRegistryInterface
     */
    private $attributeTypeRegistry;

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
     * @var string
     */
    private $defaultImage;

    /**
     * @var Formatter
     */
    private $formatter;


    /**
     * Constructor.
     *
     * @param ConstantsHelper                $constantHelper
     * @param PriceCalculator                $priceCalculator
     * @param AttributeTypeRegistryInterface $attributeTypeRegistry
     * @param LocaleProviderInterface        $localeProvider
     * @param FormatterFactory               $formatterFactory
     * @param TranslatorInterface            $translator
     * @param string                         $defaultImage
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        PriceCalculator $priceCalculator,
        AttributeTypeRegistryInterface $attributeTypeRegistry,
        LocaleProviderInterface $localeProvider,
        FormatterFactory $formatterFactory,
        TranslatorInterface $translator,
        $defaultImage = ''
    ) {
        $this->constantHelper = $constantHelper;
        $this->priceCalculator = $priceCalculator;
        $this->attributeTypeRegistry = $attributeTypeRegistry;
        $this->localeProvider = $localeProvider;
        $this->formatterFactory = $formatterFactory;
        $this->translator = $translator;
        $this->defaultImage = $defaultImage;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'product_type_label',
                [$this->constantHelper, 'renderProductTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'product_type_badge',
                [$this->constantHelper, 'renderProductTypeBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'product_reference_type_label',
                [$this->constantHelper, 'renderProductReferenceTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'product_attribute_type_label',
                [$this->constantHelper, 'renderAttributeTypeLabel']
            ),
            new \Twig_SimpleFilter(
                'product_bundle_total_price',
                [$this->priceCalculator, 'calculateBundleTotalPrice']
            ),
            new \Twig_SimpleFilter(
                'product_configurable_total_price',
                [$this->priceCalculator, 'calculateConfigurableTotalPrice']
            ),
            new \Twig_SimpleFilter(
                'product_attribute',
                [$this, 'renderProductAttribute'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'product_image',
                [$this, 'getProductImagePath']
            ),
            new \Twig_SimpleFilter(
                'product_price',
                [$this, 'getProductPrice']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'product_default_image',
                [$this, 'getDefaultImage']
            ),
            new \Twig_SimpleFunction(
                'product_types',
                [Model\ProductTypes::class, 'getTypes']
            ),
            new \Twig_SimpleFunction(
                'product_create_types',
                [Model\ProductTypes::class, 'getCreateTypes']
            ),
            new \Twig_SimpleFunction(
                'product_convert_types',
                [Model\ProductTypes::class, 'getConversionTypes']
            ),
            new \Twig_SimpleFunction(
                'attribute_create_types',
                [$this->attributeTypeRegistry, 'getChoices']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('simple_product', function (Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_SIMPLE;
            }),
            new \Twig_SimpleTest('variable_product', function (Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_VARIABLE;
            }),
            new \Twig_SimpleTest('variant_product', function (Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_VARIANT;
            }),
            new \Twig_SimpleTest('bundle_product', function (Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_BUNDLE;
            }),
            new \Twig_SimpleTest('configurable_product', function (Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE;
            }),
        ];
    }

    /**
     * Returns the default product image path.
     *
     * @return string
     */
    public function getDefaultImage()
    {
        return $this->defaultImage;
    }

    /**
     * Renders the product attribute.
     *
     * @param Model\ProductAttributeInterface $productAttribute
     *
     * @return string
     */
    public function renderProductAttribute(Model\ProductAttributeInterface $productAttribute)
    {
        $attribute = $productAttribute->getAttributeSlot()->getAttribute();

        $type = $this->attributeTypeRegistry->getType($attribute->getType());

        return $type->render($productAttribute, $this->localeProvider->getCurrentLocale());
    }

    /**
     * Returns the main image for the given product.
     *
     * @param Model\ProductInterface $product
     *
     * @return \Ekyna\Bundle\MediaBundle\Model\MediaInterface|null
     *
     * @TODO Refactor with FormBuilder::getProductImagePath()
     */
    public function getProductImagePath(Model\ProductInterface $product)
    {
        $images = $product->getMedias([MediaTypes::IMAGE]);

        if (0 == $images->count() && $product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
            /** @var Model\ProductInterface $variant */
            $variant = $product->getVariants()->first();
            $images = $variant->getMedias([MediaTypes::IMAGE]);
        }

        if (0 < $images->count()) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface $image */
            $image = $images->first();

            return $image->getMedia();
        }

        return null;
    }

    /**
     * Renders the product price.
     *
     * @param Model\ProductInterface $product
     *
     * @return string
     */
    public function getProductPrice(Model\ProductInterface $product)
    {
        // TODO user locale and currency

        $price = $this->priceCalculator->getPrice($product);

        $formatter = $this->getFormatter();
        if ($formatter->getCurrency() !== $price->getCurrency()) {
            $formatter = $this->formatterFactory->create(
                $this->localeProvider->getCurrentLocale(),
                $price->getCurrency()
            );
        }

        $current = sprintf(
            '%s&nbsp;%s',
            $formatter->currency($price->getTotal(), $price->getCurrency()),
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

        $prefix = $from ? $this->translator->trans('ekyna_commerce.subject.price_from') . ' ' : '';

        if ($price->hasDiscounts()) {
            $previous = $formatter->currency($price->getTotal(false), $price->getCurrency());

            return $prefix . sprintf('<del>%s</del> %s', $previous, $current);
        }

        return $prefix . $current;
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
