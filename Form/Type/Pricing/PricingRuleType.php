<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PricingRuleType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRuleType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('minQuantity', IntegerType::class, [
                'label' => 'ekyna_product.common.min_quantity',
                'attr'  => [
                    'min' => 1,
                ],
            ])
            ->add('percent', NumberType::class, [
                'label' => 'ekyna_product.common.percent',
                'scale' => 2,
                'attr'  => [
                    'min' => 0,
                    'max' => 100,
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_pricing_rule';
    }
}
