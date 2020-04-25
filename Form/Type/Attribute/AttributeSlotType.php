<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AttributeSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSlotType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attribute', ResourceType::class, [
                'label'    => false,
                'resource' => 'ekyna_product.attribute',
                'attr'     => [
                    'widget_col' => 12,
                ],
            ])
            ->add('required', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.required',
                'required' => false,
            ])
            ->add('naming', Type\CheckboxType::class, [
                'label'    => 'ekyna_product.attribute_slot.field.naming',
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.attribute_slot.help.naming',
                ],
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
        return 'ekyna_product_attribute_slot';
    }
}
