<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeRenderer;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ProductBundle\Service\Converter\ProductConverter;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class ProductExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'bundle_rule_type',
                [ConstantsHelper::class, 'renderBundleRuleTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_type_label',
                [ConstantsHelper::class, 'renderProductTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_type_badge',
                [ConstantsHelper::class, 'renderProductTypeBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_reference_type_label',
                [ConstantsHelper::class, 'renderProductReferenceTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_external_reference',
                [ProductHelper::class, 'renderExternalReference']
            ),
            new TwigFilter(
                'product_messages',
                [ProductReadHelper::class, 'getMessages']
            ),
            new TwigFilter(
                'product_attribute_type_label',
                [ConstantsHelper::class, 'renderAttributeTypeLabel']
            ),
            new TwigFilter(
                'product_best_seller_badge',
                [ConstantsHelper::class, 'renderProductBestSellerBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_cross_selling_badge',
                [ConstantsHelper::class, 'renderProductCrossSellingBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_price',
                [PriceRenderer::class, 'getProductPrice']
            ),
            new TwigFilter(
                'product_pricing_grid',
                [PriceRenderer::class, 'renderPricingGrid'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_components_total_price',
                [PriceRenderer::class, 'getComponentsPrice']
            ),
            new TwigFilter(
                'product_bundle_total_price',
                [PriceRenderer::class, 'getBundlePrice']
            ),
            new TwigFilter(
                'product_configurable_total_price',
                [PriceRenderer::class, 'getConfigurablePrice']
            ),
            new TwigFilter(
                'product_purchase_cost',
                [PriceRenderer::class, 'getPurchaseCost']
            ),
            new TwigFilter(
                'product_attribute',
                [AttributeRenderer::class, 'renderProductAttribute'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'attribute_type',
                [AttributeRenderer::class, 'getAttributeType']
            ),
            new TwigFilter(
                'product_image',
                [ProductHelper::class, 'getProductImagePath']
            ),
            new TwigFilter(
                'bundle_visible_products',
                [ProductHelper::class, 'getBundleVisibleProducts']
            ),
            new TwigFilter(
                'bundle_condition_product',
                [ProductHelper::class, 'getBundleRuleConditionProduct']
            ),
            new TwigFilter(
                'bundle_choice_option_groups',
                [ProductHelper::class, 'renderBundleChoiceOptionGroups'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'bundle_parents',
                [ProductReadHelper::class, 'getBundleParents']
            ),
            new TwigFilter(
                'option_parents',
                [ProductReadHelper::class, 'getOptionParents']
            ),
            new TwigFilter(
                'component_parents',
                [ProductReadHelper::class, 'getComponentParents']
            ),
            new TwigFilter(
                'related_catalogs',
                [ProductReadHelper::class, 'getRelatedCatalogs']
            ),
            new TwigFilter(
                'product_offer_list',
                [ProductReadHelper::class, 'getOfferList']
            ),
            new TwigFilter(
                'product_price_list',
                [ProductReadHelper::class, 'getPriceList']
            ),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'product_feature',
                [Features::class, 'isEnabled']
            ),
            new TwigFunction(
                'product_default_image',
                [ProductHelper::class, 'getDefaultImage']
            ),
            new TwigFunction(
                'product_types',
                [Model\ProductTypes::class, 'getTypes']
            ),
            new TwigFunction(
                'product_create_types',
                [Model\ProductTypes::class, 'getCreateTypes']
            ),
            new TwigFunction(
                'product_convert_types',
                [ProductConverter::class, 'getTargetTypes']
            ),
            new TwigFunction(
                'attribute_create_types',
                [AttributeRenderer::class, 'getChoices']
            ),
            new TwigFunction(
                'get_product_by_id',
                [ProductHelper::class, 'findOneById']
            ),
            new TwigFunction(
                'get_product_by_reference',
                [ProductHelper::class, 'findOneByReference']
            ),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('simple_product', [Model\ProductTypes::class, 'isSimpleType']),
            new TwigTest('variable_product', [Model\ProductTypes::class, 'isVariableType']),
            new TwigTest('variant_product', [Model\ProductTypes::class, 'isVariantType']),
            new TwigTest('bundle_product', [Model\ProductTypes::class, 'isBundleType']),
            new TwigTest('configurable_product', [Model\ProductTypes::class, 'isConfigurableType']),
            new TwigTest('child_product', [Model\ProductTypes::class, 'isChildType']),
            new TwigTest('parent_product', [Model\ProductTypes::class, 'isParentType']),
        ];
    }
}
