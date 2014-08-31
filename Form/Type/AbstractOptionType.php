<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * AbstractOptionType.
 *
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
            ->add('designation', 'text', array(
                'label' => 'ekyna_core.field.designation',
                'attr' => array(
                    'label_col' => 4,
                    'widget_col' => 8
                )
            ))
            ->add('reference', 'text', array(
                'label' => 'ekyna_core.field.reference',
                'attr' => array(
                    'label_col' => 4,
                    'widget_col' => 8
                )
            ))
            ->add('weight', 'integer', array(
                'label' => 'ekyna_core.field.weight',
                'attr' => array(
                    'input_group' => array('append' => 'g'),
                    'min' => 0,
                    'label_col' => 4,
                    'widget_col' => 8
                ),
            ))
            ->add('price', 'number', array(
                'label' => 'ekyna_core.field.price',
                'precision' => 5,
                'attr' => array(
                    'input_group' => array('append' => '€'),
                    'label_col' => 4,
                    'widget_col' => 8
                ),
            ))
            ->add('tax', 'ekyna_resource', array(
                'label' => 'ekyna_core.field.tax',
                'class' => 'Ekyna\Bundle\ProductBundle\Entity\Tax',
                'multiple' => false,
                'property' => 'name',
                'empty_value' => 'ekyna_core.field.tax',
                'allow_new' => $options['admin_mode'],
                'attr' => array(
                    'placeholder' => 'ekyna_core.field.tax',
                    'label_col' => 4,
                    'widget_col' => 8
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => null,
            ))
            ->setRequired(array('data_class'))
        ;
    }
}
