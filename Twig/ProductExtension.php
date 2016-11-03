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
     * {inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_product';
    }
}
