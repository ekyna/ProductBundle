<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractOptionType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractOptionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', 'text', [
                'label' => 'ekyna_core.field.designation',
                'attr' => [
                    'label_col' => 4,
                    'widget_col' => 8
                ]
            ])
            ->add('reference', 'text', [
                'label' => 'ekyna_core.field.reference',
                'attr' => [
                    'label_col' => 4,
                    'widget_col' => 8
                ]
            ])
            ->add('weight', 'integer', [
                'label' => 'ekyna_core.field.weight',
                'attr' => [
                    'input_group' => ['append' => 'g'],
                    'min' => 0,
                    'label_col' => 4,
                    'widget_col' => 8
                ],
            ])
            ->add('price', 'number', [
                'label' => 'ekyna_core.field.price',
                'precision' => 5,
                'attr' => [
                    'input_group' => ['append' => '€'],
                    'label_col' => 4,
                    'widget_col' => 8
                ],
            ])
            ->add('tax', 'ekyna_resource', [
                'label' => 'ekyna_core.field.tax',
                'class' => 'Ekyna\Bundle\OrderBundle\Entity\Tax',
                'property' => 'name',
                'empty_value' => 'ekyna_core.field.tax',
                'allow_new' => $options['admin_mode'],
                'attr' => [
                    'placeholder' => 'ekyna_core.field.tax',
                    'label_col' => 4,
                    'widget_col' => 8
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => null,
            ])
            ->setRequired(['data_class'])
        ;
    }
}
