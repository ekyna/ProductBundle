<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Form\Type as Pr;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Bundle\ProductBundle\Model;
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
    private $productProvider;

    /**
     * @var ProductFilter
     */
    private $productFilter;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $noImagePath;

    /**
     * @var \NumberFormatter
     */
    private $formatter;


    /**
     * Constructor.
     *
     * @param ProductProvider         $productProvider
     * @param ProductFilter           $productFilter
     * @param PriceCalculator         $priceCalculator
     * @param LocaleProviderInterface $localeProvider
     * @param TranslatorInterface     $translator
     * @param string                  $noImagePath
     */
    public function __construct(
        ProductProvider $productProvider,
        ProductFilter $productFilter,
        PriceCalculator $priceCalculator,
        LocaleProviderInterface $localeProvider,
        TranslatorInterface $translator,
        $noImagePath = '/bundles/ekynaproduct/img/no-image.gif'
    ) {
        $this->productProvider = $productProvider;
        $this->productFilter = $productFilter;
        $this->priceCalculator = $priceCalculator;
        $this->localeProvider = $localeProvider;
        $this->translator = $translator;
        $this->noImagePath = $noImagePath;

        $this->formatter = \NumberFormatter::create(
            $this->localeProvider->getCurrentLocale(),
            \NumberFormatter::CURRENCY
        );
    }

    /**
     * Returns the product provider.
     *
     * @return ProductProvider
     */
    public function getProvider()
    {
        return $this->productProvider;
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

        $this->buildProductForm($form, $product);

        // Quantity
        $form->add('quantity', Sf\IntegerType::class, [
            'label'       => 'ekyna_core.field.quantity',
            'constraints' => [
                new Assert\GreaterThanOrEqual(1),
            ],
            'attr'        => [
                'class' => 'sale-item-quantity',
                'min'   => 1,
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
        $this->buildProductForm($form, $bundleChoice->getProduct());

        $min = $bundleChoice->getMinQuantity();
        $max = $bundleChoice->getMaxQuantity();

        // Quantity
        $form->add('quantity', Sf\IntegerType::class, [
            'label'       => 'ekyna_core.field.quantity',
            'disabled'    => $min === $max,
            'constraints' => [
                new Assert\Range([
                    'min' => $min,
                    'max' => $max,
                ]),
            ],
            'attr'        => [
                'class' => 'sale-item-quantity',
                'min'   => $min,
                'max'   => $max,
            ],
        ]);
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
        if (null !== $variant) {
            $label = $variant->getFullTitle();

            if (0 < $netPrice = $variant->getNetPrice()) {
                // TODO User currency
                $label .= sprintf(' (%s)', $this->formatter->formatCurrency($variant->getNetPrice(), 'EUR'));
            }

            return $label;
        }

        return null;
    }

    /**
     * Returns the variant choice attributes.
     *
     * @param Model\ProductInterface|null $variant
     *
     * @return array
     */
    public function variantChoiceAttr(Model\ProductInterface $variant = null)
    {
        if (null !== $variant) {
            $groups = [];

            foreach ($variant->getOptionGroups() as $optionGroup) {
                $options = [];
                foreach ($optionGroup->getOptions() as $option) {
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
                'price'  => floatval($variant->getNetPrice()),
                'groups' => $groups,
                'thumb'  => $this->getProductImagePath($variant),
                'image'  => $this->getProductImagePath($variant, 'media_front'),
            ];

            return [
                'data-config' => json_encode($config),
            ];
        }

        return [];
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
        if (null !== $option) {
            if (null !== $product = $option->getProduct()) {
                $label = $product->getFullTitle();
                $netPrice = $product->getNetPrice();
            } else {
                $label = $option->getTitle();
                $netPrice = $option->getNetPrice();
            }

            if (0 < $netPrice) {
                // TODO User currency
                $label .= sprintf(' (%s)', $this->formatter->formatCurrency($netPrice, 'EUR'));
            }

            return $label;
        }

        return null;
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
        if (null !== $option) {
            return [
                'data-config' => json_encode($this->buildOptionConfig($option)),
            ];
        }

        return [];
    }

    /**
     * Builds the option config.
     *
     * @param Model\OptionInterface $option
     *
     * @return array
     */
    private function buildOptionConfig(Model\OptionInterface $option)
    {
        $config = [];

        if (null !== $product = $option->getProduct()) {
            $netPrice = $product->getNetPrice();
            $config['thumb'] = $this->getProductImagePath($product);
            $config['image'] = $this->getProductImagePath($product, 'media_front');
        } else {
            $netPrice = $option->getNetPrice();
        }

        $config['price'] = floatval($netPrice);

        return $config;
    }

    /**
     * Returns the main image for the given product.
     *
     * @param Model\ProductInterface $product
     * @param string                 $filter The imagine filter to apply
     *
     * @return string
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

            return $this
                ->cacheManager
                ->getBrowserPath($image->getMedia()->getPath(), $filter);
        }

        return $this->noImagePath;
    }

    /**
     * Returns the form's pricing config for the given sale item.
     *
     * @param SaleItemInterface $item
     * @param bool              $fallback
     *
     * @return array
     */
    public function getPricingConfig(SaleItemInterface $item, $fallback = true)
    {
        // Set pricing data
        $config = $this->priceCalculator->getSaleItemPricingData($item, $fallback);

        if (!empty($config['rules'])) {
            $rules = [];
            $previousQuantity = null;
            foreach ($config['rules'] as $rule) {
                if ($previousQuantity) {
                    $rule['label'] = $this->translator->trans('ekyna_product.sale_item_configure.range', [
                        '{{min}}' => $rule['quantity'],
                        '{{max}}' => $previousQuantity - 1,
                    ]);
                } else {
                    $rule['label'] = $this->translator->trans('ekyna_product.sale_item_configure.from', [
                        '{{min}}' => $rule['quantity'],
                    ]);
                }

                $rules[] = $rule;

                $previousQuantity = $rule['quantity'];
            }

            $config['rules'] = $rules;
        }

        return $config;
    }

    /**
     * Builds the product form.
     *
     * @param FormInterface          $form
     * @param Model\ProductInterface $product
     */
    private function buildProductForm(FormInterface $form, Model\ProductInterface $product)
    {
        $repository = $this->productProvider->getProductRepository();

        // Variable : add variant choice form
        if ($product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
            $repository->loadVariants($product);

            $form->add('variant', Pr\SaleItem\VariantChoiceType::class, [
                'variable' => $product,
            ]);

            // Configurable : add configuration form
        } elseif ($product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
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
     * Returns the sale item configure form translations.
     *
     * @return array
     */
    public function getTranslations()
    {
        return [
            'quantity'    => $this->translator->trans('ekyna_core.field.quantity'),
            'discount'    => $this->translator->trans('ekyna_product.sale_item_configure.discount'),
            'unit_price'  => $this->translator->trans('ekyna_product.sale_item_configure.unit_net_price'),
            'total'       => $this->translator->trans('ekyna_product.sale_item_configure.total_price'),
            'rule_table'  => $this->translator->trans('ekyna_product.sale_item_configure.rule_table'),
            'price_table' => $this->translator->trans('ekyna_product.sale_item_configure.price_table'),
        ];
    }
}
