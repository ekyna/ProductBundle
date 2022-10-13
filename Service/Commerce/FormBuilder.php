<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Form\Type as Pr;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Liip\ImagineBundle\Imagine\Cache as Imagine;
use NumberFormatter;
use Symfony\Component\Form\Extension\Core\Type as Sf;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class FormBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormBuilder
{
    use Imagine\CacheManagerAwareTrait;

    protected ProductProvider             $productProvider;
    protected ProductFilter               $productFilter;
    protected PriceCalculator             $priceCalculator;
    protected AvailabilityHelperInterface $availabilityHelper;
    protected LocaleProviderInterface     $localeProvider;
    protected TranslatorInterface         $translator;
    protected string                      $noImagePath;
    protected NumberFormatter             $formatter;

    protected ?ContextInterface $context = null;

    public function __construct(
        ProductProvider             $productProvider,
        ProductFilter               $productFilter,
        PriceCalculator             $priceCalculator,
        AvailabilityHelperInterface $availabilityHelper,
        LocaleProviderInterface     $localeProvider,
        TranslatorInterface         $translator,
        string                      $noImagePath = '/bundles/ekynaproduct/img/no-image.gif'
    ) {
        $this->productProvider = $productProvider;
        $this->productFilter = $productFilter;
        $this->priceCalculator = $priceCalculator;
        $this->availabilityHelper = $availabilityHelper;
        $this->localeProvider = $localeProvider;
        $this->translator = $translator;
        $this->noImagePath = $noImagePath;

        // TODO Use formatter factory
        $this->formatter = NumberFormatter::create(
            $this->localeProvider->getCurrentLocale(),
            NumberFormatter::CURRENCY
        );
    }

    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }

    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }

    /**
     * Builds the sale item form.
     */
    public function buildItemForm(FormInterface $form, SaleItemInterface $item): void
    {
        /** @var Model\ProductInterface $product */
        $product = $this->productProvider->resolve($item);

        $this->buildProductForm($form, $product, is_null($item->getParent()));

        $type = Units::PIECE === $product->getUnit() ? Sf\IntegerType::class : Sf\NumberType::class;

        // Quantity
        $form->add('quantity', $type, [
            'label'   => t('field.quantity', [], 'EkynaUi'),
            'decimal' => true,
            'attr'    => [
                'class' => 'sale-item-quantity',
            ],
        ]);
    }

    /**
     * Builds the sale item form view.
     */
    public function buildItemFormView(FormView $view, SaleItemInterface $item): void
    {
        /** @var Model\ProductInterface $product */
        $product = $this->productProvider->resolve($item);

        $view->vars = array_replace($view->vars, [
            'brand'       => $product->getBrand()->getTitle(),
            'product'     => $product->getFullTitle(),
            'description' => $product->getDescription(),
            'thumb'       => $this->getProductImagePath($product),
            'image'       => $this->getProductImagePath($product, 'media_front'),
        ]);
    }

    /**
     * Builds the bundle choice form.
     */
    public function buildBundleChoiceForm(FormInterface $form, Model\BundleChoiceInterface $bundleChoice): void
    {
        // TODO Disable fields for non selected bundle choices.
        $this->buildProductForm($form, $bundleChoice->getProduct(), false, $bundleChoice->getExcludedOptionGroups());

        $unit = ($product = $bundleChoice->getProduct()) ? $product->getUnit() : Units::PIECE;

        $min = Units::fixed($bundleChoice->getMinQuantity(), $unit);
        $max = Units::fixed($bundleChoice->getMaxQuantity(), $unit);

        $constraints = [
            new Assert\GreaterThanOrEqual($min),
            new Assert\LessThanOrEqual($max),
        ];

        $attr = [
            'class' => 'sale-item-quantity',
            'min'   => $min,
            'max'   => $max,
        ];

        $disabled = $min === $max;
        if ($disabled) {
            $attr['data-locked'] = '1';
        }

        $type = Units::PIECE === $unit ? Sf\IntegerType::class : Sf\NumberType::class;

        // Quantity
        $form->add('quantity', $type, [
            'label'       => t('field.quantity', [], 'EkynaUi'),
            'decimal'     => true,
            'disabled'    => $disabled,
            'constraints' => $constraints,
            'attr'        => $attr,
        ]);
    }

    /**
     * Clears the bundle choice form.
     */
    public function clearBundleChoiceForm(FormInterface $form): void
    {
        foreach (['variant', 'configuration', 'options', 'quantity'] as $field) {
            if ($form->has($field)) {
                $form->remove($field);
            }
        }
    }

    /**
     * Builds the bundle choice config.
     */
    public function buildBundleChoiceConfig(Model\ProductInterface $product): array
    {
        $config = [];

        if ($product->getType() !== Model\ProductTypes::TYPE_VARIABLE) {
            $config['pricing'] = $this->priceCalculator->buildProductPricing($product, $this->context);
            $config['availability'] = $this->availabilityHelper->getAvailability($product, false)->toArray();
        }

        return $config;
    }

    /**
     * Builds the bundle slot config.
     */
    public function buildBundleSlotConfig(Model\BundleSlotInterface $slot): array
    {
        return [
            'required' => $slot->isRequired(),
            'rules'    => $this->buildBundleRulesConfig($slot->getRules()->toArray()),
        ];
    }

    /**
     * Returns the bundle choice attributes.
     */
    public function bundleChoiceAttr(Model\BundleChoiceInterface $choice): array
    {
        return [
            'data-rules' => $this->buildBundleRulesConfig($choice->getRules()->toArray()),
        ];
    }

    /**
     * Returns the variant choice label.
     */
    public function variantChoiceLabel(Model\ProductInterface $variant = null): ?string
    {
        if (null === $variant) {
            return null;
        }

        if (!empty($label = $variant->getTitle())) {
            return $label;
        }

        return $variant->getAttributesTitle();
    }

    /**
     * Returns the variant choice attributes.
     *
     * @param array $exclude The option groups ids to exclude
     */
    public function variantChoiceAttr(
        Model\ProductInterface $variant = null,
        bool                   $root = true,
        array                  $exclude = []
    ): array {
        if (null === $variant) {
            return [];
        }

        $config = [
            // TODO discounts/taxes flags (private item)
            'pricing'      => $this->priceCalculator->buildProductPricing($variant, $this->context),
            'groups'       => $this->buildOptionsGroupsConfig($variant, $exclude),
            'thumb'        => $this->getProductImagePath($variant),
            'image'        => $this->getProductImagePath($variant, 'media_front'),
            'availability' => $this->availabilityHelper->getAvailability($variant, $root)->toArray(),
        ];

        return [
            'data-config' => $config,
        ];
    }

    /**
     * Returns the option choice label.
     */
    public function optionChoiceLabel(Model\OptionInterface $option = null): ?string
    {
        if (null === $option) {
            return null;
        }

        if (null === $product = $option->getProduct()) {
            return $option->getTitle();
        }

        if ($product->getType() === Model\ProductTypes::TYPE_VARIANT) {
            if (!empty($product->getTitle())) {
                return $product->getFullTitle(true);
            }

            if (!$option->getGroup()->isFullTitle()) {
                return $product->getAttributesTitle();
            }
        }

        return $product->getFullTitle(true);
    }

    /**
     * Returns the option choice attributes.
     *
     * @param array $exclude The option groups ids to exclude
     */
    public function optionChoiceAttr(Model\OptionInterface $option = null, array $exclude = []): array
    {
        if (null === $option) {
            return [];
        }

        return [
            'data-config' => $this->buildOptionConfig($option, $exclude),
        ];
    }

    /**
     * Returns the main image for the given product.
     *
     * @TODO Refactor with \Ekyna\Bundle\ProductBundle\Twig\ProductRenderer::getProductImagePath
     */
    public function getProductImagePath(Model\ProductInterface $product, string $filter = 'slot_choice_thumb'): string
    {
        $images = $product->getMedias([MediaTypes::IMAGE]);

        if (0 == $images->count() && $product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
            /** @var Model\ProductInterface $variant */
            $variant = $product->getVariants()->first();
            $images = $variant->getMedias([MediaTypes::IMAGE]);
        }

        if (0 < $images->count()) {
            /** @var ProductMediaInterface $image */
            $image = $images->first();

            // TODO Absolute path instead of url
            return $this
                ->cacheManager
                ->getBrowserPath($image->getMedia()->getPath(), $filter);
        }

        return $this->noImagePath;
    }

    /**
     * Returns the no image path.
     *
     * @return string
     */
    public function getNoImagePath(): string
    {
        return $this->noImagePath;
    }

    /**
     * Returns the form's pricing config for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return array
     */
    public function getFormConfig(SaleItemInterface $item): array
    {
        $config = [];

        /** @var Model\ProductInterface $subject */
        if (null !== $subject = $item->getSubjectIdentity()->getSubject()) {
            $skippedTypes = [Model\ProductTypes::TYPE_VARIABLE, Model\ProductTypes::TYPE_CONFIGURABLE];
            if (!in_array($subject->getType(), $skippedTypes, true)) {
                $config['pricing'] = $this->priceCalculator->buildProductPricing($subject, $this->context);
                $config['availability'] = $this->availabilityHelper->getAvailability($subject)->toArray();
            }
        }

        return $config;
    }

    /**
     * Returns the sale item configure form globals.
     *
     * @return array
     */
    public function getFormGlobals(): array
    {
        return [
            'mode'     => $this->context->getVatDisplayMode(),
            'currency' => $this->context->getCurrency()->getCode(),
        ];
    }

    /**
     * Returns the sale item configure form translations.
     */
    public function getFormTranslations(): array
    {
        return [
            'quantity'    => $this->translate('field.quantity', [], 'EkynaCore'),
            'discount'    => $this->translate('sale_item_configure.discount', [], 'EkynaProduct'),
            'unit_price'  => $this->translate('sale_item_configure.unit_price', [], 'EkynaProduct'),
            'total'       => $this->translate('sale_item_configure.total_price', [], 'EkynaProduct'),
            'rule_table'  => $this->translate('sale_item_configure.rule_table', [], 'EkynaProduct'),
            'price_table' => $this->translate('sale_item_configure.price_table', [], 'EkynaProduct'),
            'ati'         => $this->translate('pricing.vat_display_mode.ati', [], 'EkynaCommerce'),
            'net'         => $this->translate('pricing.vat_display_mode.net', [], 'EkynaCommerce'),
            'from'        => $this->translate('pricing_rule.field.from', [], 'EkynaProduct'),
            'range'       => $this->translate('pricing_rule.field.range', [], 'EkynaProduct'),
            'add_to_cart' => $this->translate('button.add_to_cart', [], 'EkynaCommerce'),
            'pre_order'   => $this->translate('button.pre_order', [], 'EkynaCommerce'),
        ];
    }

    /**
     * Translates the given message.
     */
    public function translate(string $id, array $parameters = [], string $domain = null): string
    {
        return $this->translator->trans($id, $parameters, $domain);
    }

    /**
     * Builds the product form.
     *
     * @param FormInterface          $form    The form
     * @param Model\ProductInterface $product The product
     * @param bool                   $root    Whether this is a root sale item
     * @param array                  $exclude The option groups ids to exclude
     */
    protected function buildProductForm(
        FormInterface          $form,
        Model\ProductInterface $product,
        bool                   $root = true,
        array                  $exclude = []
    ): void {
        $repository = $this->productProvider->getRepository();

        if (!$repository instanceof ProductRepositoryInterface) {
            throw new UnexpectedTypeException($repository, ProductRepositoryInterface::class);
        }

        // Variable : add variant choice form
        if (in_array($product->getType(), [Model\ProductTypes::TYPE_VARIABLE, Model\ProductTypes::TYPE_VARIANT],
            true)) {
            $variable = $product->getType() === Model\ProductTypes::TYPE_VARIANT ? $product->getParent() : $product;

            $repository->loadVariants($variable);

            $form->add('variant', Pr\SaleItem\VariantChoiceType::class, [
                'variable'        => $variable,
                'root_item'       => $root,
                'exclude_options' => $exclude,
            ]);
        } // Configurable : add configuration form
        elseif ($product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
            $repository->loadConfigurableSlots($product);

            foreach ($product->getBundleSlots() as $slot) {
                foreach ($slot->getChoices() as $choice) {
                    $repository->loadMedias($choice->getProduct());
                }
            }

            $form->add('configuration', Pr\SaleItem\ConfigurableSlotsType::class);
        }

        $repository->loadOptions($product);
        $form->add('options', Pr\SaleItem\OptionGroupsType::class, [
            'exclude_options' => $exclude,
        ]);
    }

    /**
     * Builds the option config.
     *
     * @param Model\OptionInterface $option  The option
     * @param array                 $exclude The option group ids to exclude
     * @param bool                  $cascade Whether to allow option cascading
     */
    protected function buildOptionConfig(
        Model\OptionInterface $option,
        array                 $exclude = [],
        bool                  $cascade = true
    ): array {
        $config = [];

        if (null !== $product = $option->getProduct()) {
            $config['availability'] = $this->availabilityHelper->getAvailability($product, false)->toArray();
            $config['thumb'] = $this->getProductImagePath($product);
            $config['image'] = $this->getProductImagePath($product, 'media_front');
            if ($cascade && $option->isCascade()) {
                $config['groups'] = $this->buildOptionsGroupsConfig($product, $exclude, false);
            }
        }

        // TODO discounts/taxes flags (private item)
        $config['pricing'] = $this->priceCalculator->buildOptionPricing($option, $this->context);

        return $config;
    }

    /**
     * Builds the product options groups config.
     *
     * @param Model\ProductInterface $product The product
     * @param array                  $exclude The option group ids to exclude
     * @param bool                   $cascade Whether to allow option cascading
     */
    protected function buildOptionsGroupsConfig(
        Model\ProductInterface $product,
        array                  $exclude = [],
        bool                   $cascade = true
    ): array {
        $groups = [];

        $optionGroups = $this->productFilter->getOptionGroups($product, $exclude);

        /** @var Model\OptionGroupInterface $optionGroup */
        foreach ($optionGroups as $optionGroup) {
            $groupOptions = $this->productFilter->getGroupOptions($optionGroup);
            $options = [];
            /** @var Model\OptionInterface $option */
            foreach ($groupOptions as $option) {
                $options[] = [
                    'id'     => $option->getId(),
                    'label'  => $this->optionChoiceLabel($option),
                    'config' => $this->buildOptionConfig($option, $exclude, $cascade),
                ];
            }
            $groups[] = [
                'id'          => $optionGroup->getId(),
                'label'       => $optionGroup->getTitle(),
                'required'    => $optionGroup->isRequired(),
                'placeholder' => $this->translate('sale_item_configure.choose_option', [], 'EkynaProduct'),
                'position'    => $optionGroup->getPosition(),
                'options'     => $options,
            ];
        }

        return $groups;
    }

    /**
     * Builds the bundle rules config.
     */
    protected function buildBundleRulesConfig(array $rules): array
    {
        $config = [];

        /** @var Model\BundleRuleInterface $rule */
        foreach ($rules as $rule) {
            $config[$rule->getType()] = [];
            foreach ($rule->getConditions() as $condition) {
                $config[$rule->getType()][] = [
                    's' => $condition['slot'],
                    'c' => $condition['choice'],
                ];
            }
        }

        return $config;
    }
}
