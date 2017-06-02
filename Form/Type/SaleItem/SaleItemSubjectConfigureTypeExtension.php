<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemSubjectConfigureType;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SaleItemSubjectConfigureTypeExtension
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemSubjectConfigureTypeExtension extends AbstractTypeExtension
{
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;


    /**
     * Constructor.
     *
     * @param PriceCalculator $priceCalculator
     */
    public function __construct(PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (null === $item = $form->getData()) {
            return;
        }

        $pricing = $this->priceCalculator->getSaleItemPricingData($item, !$options['admin_mode']);
        if (!empty($pricing['rules'])) {
            $rules = [];
            foreach ($pricing['rules'] as $quantity => $percent) {
                $rules[] = [
                    'quantity' => $quantity,
                    'percent' => $percent,
                ];
            }
            $pricing['rules'] = $rules;
        }

        $view->vars['pricing'] = $pricing;
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return SaleItemSubjectConfigureType::class;
    }
}
