<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

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
                'label' => 'ekyna_product.pricing_rule.field.min_quantity',
                'attr' => [
                    'min' => 1,
                ]
            ])
            ->add('percent', NumberType::class, [
                'label' => 'ekyna_product.pricing_rule.field.percent',
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ]
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
