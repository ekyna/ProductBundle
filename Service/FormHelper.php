<?php

namespace Ekyna\Bundle\ProductBundle\Service;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

/**
 * Class FormHelper
 * @package Ekyna\Bundle\ProductBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormHelper
{
    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var \NumberFormatter
     */
    private $formatter;

    /**
     * Constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     */
    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;

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
            $label = $variant->getTitle();

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

            return [
                'data-price'  => $variant->getNetPrice(),
                'data-option-groups' => json_encode($groups),
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
}
