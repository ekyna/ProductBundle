<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;


/**
 * Class ProductExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     */
    public function __construct(ConstantsHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
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
                'product_attributes',
                [$this, 'transformProductAttributes']
            ),
            new \Twig_SimpleFilter(
                'product_bundle_total_price',
                [$this, 'calculateBundleTotalPrice']
            ),
            new \Twig_SimpleFilter(
                'product_configurable_total_price',
                [$this, 'calculateConfigurableTotalPrice']
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
                'get_product_types',
                [Model\ProductTypes::class, 'getTypes']
            ),
            new \Twig_SimpleFunction(
                'get_product_create_types',
                [Model\ProductTypes::class, 'getCreateTypes']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('simple_product', function(Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_SIMPLE;
            }),
            new \Twig_SimpleTest('variable_product', function(Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_VARIABLE;
            }),
            new \Twig_SimpleTest('variant_product', function(Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_VARIANT;
            }),
            new \Twig_SimpleTest('bundle_product', function(Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_BUNDLE;
            }),
            new \Twig_SimpleTest('configurable_product', function(Model\ProductInterface $product) {
                return $product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE;
            }),
        );
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
                'attributes' => $groupAttributes
            ];
        }

        return $attributes;
    }

    /**
     * Calculates the product (bundle) total price.
     *
     * @param Model\ProductInterface $product
     *
     * @return float|int
     *
     * @todo Move to a dedicated service
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
     * @todo Move to a dedicated service
     * @todo The product (configurable) min price should be processed and persisted during update (flush)
     */
    public function calculateConfigurableTotalPrice(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertConfigurable($product);

        $total = 0;

        foreach ($product->getBundleSlots() as $slot) {
            $lowerChoice = $lowerPrice = null;

            // TODO Check compatibility
            foreach ($slot->getChoices() as $choice) {
                $price = $choice->getProduct()->getNetPrice() * $choice->getMinQuantity();
                if (null === $lowerPrice || $price < $lowerPrice) {
                    $lowerChoice = $choice;
                }
            }

            $total += $lowerChoice->getProduct()->getNetPrice() * $lowerChoice->getMinQuantity();
        }

        return $total;
    }


    /**
     * {inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_product';
    }
}
