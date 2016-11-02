<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\TaxGroupChoiceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OptionType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Type\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'sizing' => 'sm',
                'required' => false,
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.reference',
                ],
            ])
            ->add('netPrice', Type\NumberType::class, [
                'label'  => 'ekyna_product.product.field.net_price',
                'sizing' => 'sm',
                'scale'  => 5,
                'attr'   => [
                    'placeholder' => 'ekyna_product.product.field.net_price',
                    'input_group' => ['append' => '€'],
                ],
            ])
            // TODO weight
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'sizing' => 'sm',
            ])
            ->add('position', Type\HiddenType::class,[
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_option';
    }
}
