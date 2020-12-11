<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ProductBundle\Service\Converter\ProductConverter;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceRenderer;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Features
     */
    private $features;

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
     * @param ProductRepositoryInterface     $productRepository
     * @param Features                          $features
     * @param string                         $defaultImage
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        PriceRenderer $priceRenderer,
        AttributeTypeRegistryInterface $attributeTypeRegistry,
        LocaleProviderInterface $localeProvider,
        ProductRepositoryInterface $productRepository,
        Features $features,
        string $defaultImage = '/bundles/ekynaproduct/img/no-image.gif'
    ) {
        $this->constantHelper = $constantHelper;
        $this->priceRenderer = $priceRenderer;
        $this->attributeTypeRegistry = $attributeTypeRegistry;
        $this->localeProvider = $localeProvider;
        $this->productRepository = $productRepository;
        $this->features = $features;
        $this->defaultImage = $defaultImage;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'bundle_rule_type',
                [$this->constantHelper, 'renderBundleRuleTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_type_label',
                [$this->constantHelper, 'renderProductTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_type_badge',
                [$this->constantHelper, 'renderProductTypeBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_reference_type_label',
                [$this->constantHelper, 'renderProductReferenceTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_attribute_type_label',
                [$this->constantHelper, 'renderAttributeTypeLabel']
            ),
            new TwigFilter(
                'product_best_seller_badge',
                [$this->constantHelper, 'renderProductBestSellerBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_cross_selling_badge',
                [$this->constantHelper, 'renderProductCrossSellingBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_price',
                [$this, 'getProductPrice']
            ),
            new TwigFilter(
                'product_pricing_grid',
                [$this->priceRenderer, 'renderPricingGrid'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_components_total_price',
                [$this->priceRenderer, 'getComponentsPrice']
            ),
            new TwigFilter(
                'product_bundle_total_price',
                [$this->priceRenderer, 'getBundlePrice']
            ),
            new TwigFilter(
                'product_configurable_total_price',
                [$this->priceRenderer, 'getConfigurablePrice']
            ),
            new TwigFilter(
                'product_purchase_cost',
                [$this->priceRenderer, 'getPurchaseCost']
            ),
            new TwigFilter(
                'product_attribute',
                [$this, 'renderProductAttribute'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'product_image',
                [$this, 'getProductImagePath']
            ),
            new TwigFilter(
                'bundle_visible_products',
                [$this, 'getBundleVisibleProducts']
            ),
            new TwigFilter(
                'bundle_condition_product',
                [$this, 'getBundleRuleConditionProduct']
            ),
            new TwigFilter(
                'bundle_choice_option_groups',
                [$this, 'renderBundleChoiceOptionGroups'],
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
            new TwigFunction(
                'product_feature',
                [$this->features, 'isEnabled']
            ),
            new TwigFunction(
                'product_default_image',
                [$this, 'getDefaultImage']
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
                [$this->attributeTypeRegistry, 'getChoices']
            ),
            new TwigFunction(
                'get_product_by_id',
                [$this->productRepository, 'findOneById']
            ),
            new TwigFunction(
                'get_product_by_reference',
                [$this->productRepository, 'findOneByReference']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
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
     * Returns the product price display.
     *
     * @param Model\ProductInterface $product
     * @param array                  $options
     *
     * @return Model\PriceDisplay
     */
    public function getProductPrice(Model\ProductInterface $product, array $options = []): Model\PriceDisplay
    {
        $options = array_replace([
            'context' => null,
            'discount' => true,
            'extended' => false,
        ], $options);

        return $this->priceRenderer->getProductPrice(
            $product,
            $options['context'],
            $options['discount'],
            $options['extended']
        );
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
     * Returns the bundle visible products.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    public function getBundleVisibleProducts(Model\ProductInterface $product)
    {
        Model\ProductTypes::assertBundle($product);

        $visible = [];

        foreach ($product->getBundleSlots() as $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();
            $choiceProduct = $choice->getProduct();
            if ($choiceProduct->isVisible() && !$choice->isHidden()) {
                $visible[] = [
                    'quantity' => $choice->getMinQuantity(),
                    'product'  => $choiceProduct,
                ];
            }
        }

        return $visible;
    }

    /**
     * Renders the bundle rule condition product.
     *
     * @param array                  $condition
     * @param Model\ProductInterface $bundle
     *
     * @return Model\ProductInterface
     */
    public function getBundleRuleConditionProduct(
        array $condition,
        Model\ProductInterface $bundle
    ): ?Model\ProductInterface {
        foreach ($bundle->getBundleSlots() as $si => $slot) {
            if ($si != $condition['slot']) {
                continue;
            }

            foreach ($slot->getChoices() as $ci => $choice) {
                if ($ci != $condition['choice']) {
                    continue;
                }

                return $choice->getProduct();
            }
        }

        return null;
    }

    /**
     * Renders the bundle choice option groups badge.
     *
     * @param Model\BundleChoiceInterface $choice
     *
     * @return string
     */
    public function renderBundleChoiceOptionGroups(Model\BundleChoiceInterface $choice): string
    {
        $product = $choice->getProduct();

        $excluded = $choice->getExcludedOptionGroups();
        $groups = $product->resolveOptionGroups([], true);

        $all = $exc = 0;
        $popover = '<ul class="list-unstyled">';
        foreach ($groups as $group) {
            $all++;
            if (in_array($group->getId(), $excluded)) {
                $icon = '<i class="fa fa-remove text-danger"/>';
                $exc++;
            } else {
                $icon = '<i class="fa fa-check text-success"/>';
            }

            $label = sprintf(
                '[%s] %s',
                $group->isRequired() ? 'Required' : 'Optional',
                addcslashes($group->getName(), "'")
            );

            $popover .= "<li>$icon $label</li>";
        }

        $popover .= "</ul>";

        if ($exc === 0) {
            $theme = 'success';
            $icon = '<i class="fa fa-check"/>';
        } elseif ($all === $exc) {
            $theme = 'danger';
            $icon = '<i class="fa fa-remove"/>';
        } else {
            $theme = 'warning';
            $icon = '<i class="fa fa-check"/>';
        }

        if ($all === 0) {
            $popover = '';
        } else {
            $popover = " data-toggle=\"popover\" data-content='$popover'";
        }

        /** @noinspection HtmlUnknownAttribute */
        return sprintf(
            '<span class="label label-%s"%s>%s</span>',
            $theme, $popover, $icon
        );
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
