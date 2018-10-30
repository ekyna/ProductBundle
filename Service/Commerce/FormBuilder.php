<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Form\Type as Pr;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Liip\ImagineBundle\Imagine\Cache as Imagine;
use Symfony\Component\Form\Extension\Core\Type as Sf;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FormBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormBuilder
{
    use Imagine\CacheManagerAwareTrait;

    /**
     * @var ProductProvider
     */
    protected $productProvider;

    /**
     * @var ProductFilter
     */
    protected $productFilter;

    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;

    /**
     * @var AvailabilityHelperInterface
     */
    protected $availabilityHelper;

    /**
     * @var LocaleProviderInterface
     */
    protected $localeProvider;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $noImagePath;

    /**
     * @var \NumberFormatter
     */
    protected $formatter;

    /**
     * @var ContextInterface
     */
    protected $context;


    /**
     * Constructor.
     *
     * @param ProductProvider             $productProvider
     * @param ProductFilter               $productFilter
     * @param PriceCalculator             $priceCalculator
     * @param AvailabilityHelperInterface $availabilityHelper
     * @param LocaleProviderInterface     $localeProvider
     * @param TranslatorInterface         $translator
     * @param string                      $noImagePath
     */
    public function __construct(
        ProductProvider $productProvider,
        ProductFilter $productFilter,
        PriceCalculator $priceCalculator,
        AvailabilityHelperInterface $availabilityHelper,
        LocaleProviderInterface $localeProvider,
        TranslatorInterface $translator,
        $noImagePath = '/bundles/ekynaproduct/img/no-image.gif'
    ) {
        $this->productProvider = $productProvider;
        $this->productFilter = $productFilter;
        $this->priceCalculator = $priceCalculator;
        $this->availabilityHelper = $availabilityHelper;
        $this->localeProvider = $localeProvider;
        $this->translator = $translator;
        $this->noImagePath = $noImagePath;

        $this->formatter = \NumberFormatter::create(
            $this->localeProvider->getCurrentLocale(),
            \NumberFormatter::CURRENCY
        );
    }

    /**
     * Sets the context.
     *
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the context.
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Builds the sale item form.
     *
     * @param FormInterface     $form
     * @param SaleItemInterface $item
     */
    public function buildItemForm(FormInterface $form, SaleItemInterface $item)
    {
        /** @var Model\ProductInterface $product */
        $product = $this->productProvider->resolve($item);

        $this->buildProductForm($form, $product, is_null($item->getParent()));

        // Quantity
        // TODO packaging
        $form->add('quantity', Sf\IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr'  => [
                'class' => 'sale-item-quantity',
            ],
        ]);
    }

    /**
     * Builds the sale item form view.
     *
     * @param FormView          $view
     * @param SaleItemInterface $item
     */
    public function buildItemFormView(FormView $view, SaleItemInterface $item)
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
     *
     * @param FormInterface               $form
     * @param Model\BundleChoiceInterface $bundleChoice
     */
    public function buildBundleChoiceForm(FormInterface $form, Model\BundleChoiceInterface $bundleChoice)
    {
        // TODO Disable fields for non selected bundle choices.
        $this->buildProductForm($form, $bundleChoice->getProduct(), false);

        // TODO Use packaging format (+ integer/number field type)

        $min = $bundleChoice->getMinQuantity();
        $max = $bundleChoice->getMaxQuantity();

        $constraints = [
            new Assert\Range([
                'min' => $min,
                'max' => $max,
            ]),
        ];

        $attr = [
            'class' => 'sale-item-quantity',
            'min'   => $min,
            'max'   => $max,
        ];

        $disabled = $min === $max;
        if ($disabled) {
            $attr['data-locked'] = "1";
        }

        // Quantity
        $form->add('quantity', Sf\IntegerType::class, [
            'label'       => 'ekyna_core.field.quantity',
            'disabled'    => $disabled,
            'constraints' => $constraints,
            'attr'        => $attr,
        ]);
    }

    /**
     * Clears the bundle choice form.
     *
     * @param FormInterface $form
     */
    public function clearBundleChoiceForm(FormInterface $form)
    {
        foreach (['variant', 'configuration', 'options', 'quantity'] as $field) {
            if ($form->has($field)) {
                $form->remove($field);
            }
        }
    }

    /**
     * Builds the bundle choice config.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    public function buildBundleChoiceConfig(Model\ProductInterface $product)
    {
        $config = [];

        if ($product->getType() !== Model\ProductTypes::TYPE_VARIABLE) {
            $config['pricing'] = $this->priceCalculator->buildProductPricing($product, $this->context);
            $config['availability'] = $this->availabilityHelper->getAvailability($product, false)->toArray();
        }

        return $config;
    }

    /**
     * Returns the variant choice label.
     *
     * @param Model\ProductInterface|null $variant
     *
     * @return null|string
     */
    public function variantChoiceLabel(Model\ProductInterface $variant = null)
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
     * @param Model\ProductInterface|null $variant
     * @param bool                        $root
     *
     * @return array
     */
    public function variantChoiceAttr(Model\ProductInterface $variant = null, bool $root = true)
    {
        if (null === $variant) {
            return [];
        }

        $groups = [];

        $optionGroups = $this->productFilter->getOptionGroups($variant);

        /** @var Model\OptionGroupInterface $optionGroup */
        foreach ($optionGroups as $optionGroup) {
            $groupOptions = $this->productFilter->getGroupOptions($optionGroup);
            $options = [];
            /** @var Model\OptionInterface $option */
            foreach ($groupOptions as $option) {
                $options[] = [
                    'id'     => $option->getId(),
                    'label'  => $this->optionChoiceLabel($option),
                    'config' => $this->buildOptionConfig($option),
                ];
            }
            $groups[] = [
                'id'          => $optionGroup->getId(),
                'type'        => $optionGroup->getProduct()->getType(),
                'label'       => $optionGroup->getTitle(),
                'required'    => $optionGroup->isRequired(),
                'placeholder' => 'Choisissez une option', // TODO trans
                'options'     => $options,
            ];
        }

        $config = [
            // TODO discounts/taxes flags (private item)
            'pricing'      => $this->priceCalculator->buildProductPricing($variant, $this->context),
            'groups'       => $groups,
            'thumb'        => $this->getProductImagePath($variant),
            'image'        => $this->getProductImagePath($variant, 'media_front'),
            'availability' => $this->availabilityHelper->getAvailability($variant, $root)->toArray(),
        ];

        return [
            //'data-config'  => $this->jsonEncode($config),
            'data-config' => $config,
        ];
    }

    /**
     * Returns the option choice label.
     *
     * @param Model\OptionInterface|null $option
     *
     * @return null|string
     */
    public function optionChoiceLabel(Model\OptionInterface $option = null)
    {
        if (null === $option) {
            return null;
        }

        if (null === $product = $option->getProduct()) {
            return $option->getTitle();
        }

        if ($product->getType() === Model\ProductTypes::TYPE_VARIANT) {
            if (!empty($label = $product->getTitle())) {
                return $label;
            }

            if (!$option->getGroup()->isFullTitle()) {
                return $product->getAttributesTitle();
            }
        }

        return $product->getFullTitle();
    }

    /**
     * Returns the option choice attributes.
     *
     * @param Model\OptionInterface|null $option
     *
     * @return array
     */
    public function optionChoiceAttr(Model\OptionInterface $option = null)
    {
        if (null === $option) {
            return [];
        }

        return [
            //'data-config'  => $this->jsonEncode($this->buildOptionConfig($option)),
            'data-config' => $this->buildOptionConfig($option),
        ];
    }

    /**
     * Returns the main image for the given product.
     *
     * @param Model\ProductInterface $product
     * @param string                 $filter The imagine filter to apply
     *
     * @return string
     *
     * @TODO Refactor with (twig) ProductExtension::getProductImagePath()
     */
    public function getProductImagePath(Model\ProductInterface $product, $filter = 'slot_choice_thumb')
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
    public function getNoImagePath()
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
    public function getFormConfig(SaleItemInterface $item)
    {
        $config = [];

        /** @var Model\ProductInterface $subject */
        if (null !== $subject = $item->getSubjectIdentity()->getSubject()) {
            $skippedTypes = [Model\ProductTypes::TYPE_VARIABLE, Model\ProductTypes::TYPE_CONFIGURABLE];
            if (!in_array($subject->getType(), $skippedTypes, true)) {
//            if (Model\ProductTypes::isChildType($subject->getType())) {
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
    public function getFormGlobals()
    {
        return [
            'mode'     => $this->context->getVatDisplayMode(),
            'currency' => $this->context->getCurrency()->getCode(),
        ];
    }

    /**
     * Returns the sale item configure form translations.
     *
     * @return array
     */
    public function getFormTranslations()
    {
        return [
            'quantity'    => $this->translate('ekyna_core.field.quantity'),
            'discount'    => $this->translate('ekyna_product.sale_item_configure.discount'),
            'unit_price'  => $this->translate('ekyna_product.sale_item_configure.unit_price'),
            'total'       => $this->translate('ekyna_product.sale_item_configure.total_price'),
            'rule_table'  => $this->translate('ekyna_product.sale_item_configure.rule_table'),
            'price_table' => $this->translate('ekyna_product.sale_item_configure.price_table'),
            'ati'         => $this->translate('ekyna_commerce.pricing.vat_display_mode.ati'),
            'net'         => $this->translate('ekyna_commerce.pricing.vat_display_mode.net'),
            // 'min_quantity' => $this->translate('ekyna_product.common.min_quantity'),
            // 'max_quantity' => $this->translate('ekyna_product.common.max_quantity'),
        ];
    }

    /**
     * Translates the given message.
     *
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     *
     * @return string
     */
    public function translate($id, array $parameters = [], $domain = null)
    {
        return $this->translator->trans($id, $parameters, $domain);
    }

    /**
     * Builds the product form.
     *
     * @param FormInterface          $form
     * @param Model\ProductInterface $product
     * @param bool                   $root
     */
    protected function buildProductForm(FormInterface $form, Model\ProductInterface $product, bool $root = true)
    {
        $repository = $this->productProvider->getRepository();

        // Variable : add variant choice form
        if (in_array($product->getType(), [Model\ProductTypes::TYPE_VARIABLE, Model\ProductTypes::TYPE_VARIANT],
            true)) {
            $variable = $product->getType() === Model\ProductTypes::TYPE_VARIANT ? $product->getParent() : $product;

            $repository->loadVariants($variable);

            $form->add('variant', Pr\SaleItem\VariantChoiceType::class, [
                'variable'  => $variable,
                'root_item' => $root,
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
        $form->add('options', Pr\SaleItem\OptionGroupsType::class);
    }

    /**
     * Builds the option config.
     *
     * @param Model\OptionInterface $option
     *
     * @return array
     */
    protected function buildOptionConfig(Model\OptionInterface $option)
    {
        $config = [];

        if (null !== $product = $option->getProduct()) {
            $config['availability'] = $this->availabilityHelper->getAvailability($product, false)->toArray();
            $config['thumb'] = $this->getProductImagePath($product);
            $config['image'] = $this->getProductImagePath($product, 'media_front');
        }

        // TODO discounts/taxes flags (private item)
        $config['pricing'] = $this->priceCalculator->buildOptionPricing($option, $this->context);

        return $config;
    }
}
