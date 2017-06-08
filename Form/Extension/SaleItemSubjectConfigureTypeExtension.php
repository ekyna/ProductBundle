<?php

namespace Ekyna\Bundle\ProductBundle\Form\Extension;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemSubjectConfigureType;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SaleItemSubjectConfigureTypeExtension
 * @package Ekyna\Bundle\ProductBundle\Form\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemSubjectConfigureTypeExtension extends AbstractTypeExtension
{
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param PriceCalculator     $priceCalculator
     * @param TranslatorInterface $translator
     */
    public function __construct(PriceCalculator $priceCalculator, TranslatorInterface $translator)
    {
        $this->priceCalculator = $priceCalculator;
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (null === $item = $form->getData()) {
            return;
        }

        // TODO abort if subject is not an instance of ProductInterface

        $config = $this->priceCalculator->getSaleItemPricingData($item, !$options['admin_mode']);

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
        }

        $config['headers'] = [
            'quantity' => $this->translator->trans('ekyna_core.field.quantity'),
            'percent'  => $this->translator->trans('ekyna_product.sale_item_configure.discount'),
            'price'    => $this->translator->trans('ekyna_product.sale_item_configure.unit_net_price'),
        ];

        $view->vars['pricing'] = $config;
        $view->vars['attr']['data-config'] = json_encode($config);
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return SaleItemSubjectConfigureType::class;
    }
}
