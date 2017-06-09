<?php

namespace Ekyna\Bundle\ProductBundle\Service;

use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Liip\ImagineBundle\Imagine\Cache as Imagine;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class FormHelper
 * @package Ekyna\Bundle\ProductBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormHelper implements Imagine\CacheManagerAwareInterface
{
    use Imagine\CacheManagerAwareTrait;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

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
     * @param LocaleProviderInterface $localeProvider
     * @param PriceCalculator         $priceCalculator
     * @param TranslatorInterface     $translator
     * @param string                  $noImagePath
     */
    public function __construct(
        LocaleProviderInterface $localeProvider,
        PriceCalculator $priceCalculator,
        TranslatorInterface $translator,
        $noImagePath = 'bundles/ekynaproduct/img/no-image.gif'
    ) {
        $this->localeProvider = $localeProvider;
        $this->priceCalculator = $priceCalculator;
        $this->translator = $translator;
        $this->noImagePath = $noImagePath;

        $this->formatter = \NumberFormatter::create(
            $this->localeProvider->getCurrentLocale(),
            \NumberFormatter::CURRENCY
        );
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
                        'id'    => $option->getId(),
                        'label' => $this->optionChoiceLabel($option),
                        'price' => $option->getNetPrice(),
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
                'price'  => $variant->getNetPrice(),
                'groups' => $groups,
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
                $label = $product->getTitle();
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
            if (null !== $product = $option->getProduct()) {
                $netPrice = $product->getNetPrice();
            } else {
                $netPrice = $option->getNetPrice();
            }

            return [
                'data-price' => $netPrice,
            ];
        }

        return [];
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
            foreach (array_reverse($config['rules'], true) as $quantity => $percent) {
                if ($previousQuantity) {
                    $label = $this->translator->trans('ekyna_product.sale_item_configure.range', [
                        '{{min}}' => $quantity,
                        '{{max}}' => $previousQuantity - 1,
                    ]);
                } else {
                    $label = $this->translator->trans('ekyna_product.sale_item_configure.from', [
                        '{{min}}' => $quantity,
                    ]);
                }

                $rules[] = [
                    'label'    => $label,
                    'quantity' => $quantity,
                    'percent'  => $percent,
                ];

                $previousQuantity = $quantity;
            }

            $config['rules'] = array_reverse($rules);

            $config['headers'] = [
                'quantity' => $this->translator->trans('ekyna_core.field.quantity'),
                'percent'  => $this->translator->trans('ekyna_product.sale_item_configure.discount'),
                'price'    => $this->translator->trans('ekyna_product.sale_item_configure.unit_net_price'),
            ];
        }

        return $config;
    }
}
