<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;

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
     * @var string
     */
    private $defaultImage;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     * @param PriceCalculator $priceCalculator
     * @param string          $defaultImage
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        PriceCalculator $priceCalculator,
        $defaultImage = ''
    ) {
        $this->constantHelper = $constantHelper;
        $this->priceCalculator = $priceCalculator;
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
                'product_bundle_total_price',
                [$this->priceCalculator, 'calculateBundleTotalPrice']
            ),
            new \Twig_SimpleFilter(
                'product_configurable_total_price',
                [$this->priceCalculator, 'calculateConfigurableTotalPrice']
            ),
            new \Twig_SimpleFilter(
                'product_attributes',
                [$this, 'transformProductAttributes']
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
     * Transforms the product attributes to an array of attribute slots.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    public function transformProductAttributes(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertVariant($product);

        $attributes = [];
        $slots = $product->getParent()->getAttributeSet()->getSlots();

        foreach ($slots as $slot) {
            $group = $slot->getGroup();

            $groupAttributes = [];
            foreach ($product->getAttributes() as $attribute) {
                if ($group === $attribute->getGroup()) {
                    $groupAttributes[] = $attribute;
                }
            }

            $attributes[] = [
                'group'      => $group,
                'attributes' => $groupAttributes,
            ];
        }

        return $attributes;
    }

    /**
     * {inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_product';
    }
}
