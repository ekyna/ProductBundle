<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OptionGroupType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label'  => 'ekyna_core.field.name',
                'sizing' => 'sm',
            ])
            ->add('required', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.required',
                'sizing'   => 'sm',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('options', CollectionType::class, [
                'label'           => 'ekyna_product.option.label.plural',
                'prototype_name'  => '__option__',
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => true,
                'entry_type'      => OptionType::class,
                'add_button_text' => 'ekyna_product.option.button.add',
            ])
            ->add('position', Type\HiddenType::class, [
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
        return 'ekyna_product_option_group';
    }
}
