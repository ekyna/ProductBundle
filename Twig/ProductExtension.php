<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceRenderer;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

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
     * @var PriceRenderer
     */
    private $priceRenderer;

    /**
     * @var AttributeTypeRegistryInterface
     */
    private $attributeTypeRegistry;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var string
     */
    private $defaultImage;


    /**
     * Constructor.
     *
     * @param ConstantsHelper                $constantHelper
     * @param PriceRenderer                  $priceRenderer
     * @param AttributeTypeRegistryInterface $attributeTypeRegistry
     * @param LocaleProviderInterface        $localeProvider
     * @param string                         $defaultImage
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        PriceRenderer $priceRenderer,
        AttributeTypeRegistryInterface $attributeTypeRegistry,
        LocaleProviderInterface $localeProvider,
        $defaultImage = '/bundles/ekynaproduct/img/no-image.gif'
    ) {
        $this->constantHelper = $constantHelper;
        $this->priceRenderer = $priceRenderer;
        $this->attributeTypeRegistry = $attributeTypeRegistry;
        $this->localeProvider = $localeProvider;
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
                'product_price',
                [$this->priceRenderer, 'getProductPrice'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'product_discount_grid',
                [$this->priceRenderer, 'getDiscountGrid'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'product_bundle_total_price',
                [$this->priceRenderer, 'getBundlePrice']
            ),
            new \Twig_SimpleFilter(
                'product_configurable_total_price',
                [$this->priceRenderer, 'getConfigurablePrice']
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
     * Returns the default product image path.
     *
     * @return string
     */
    public function getDefaultImage()
    {
        return $this->defaultImage;
    }
}
