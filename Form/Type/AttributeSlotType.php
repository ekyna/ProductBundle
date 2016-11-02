<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AttributeSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSlotType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $attributeGroupClass;


    /**
     * Constructor.
     *
     * @param string $attributeSlotClass
     * @param string $attributeGroupClass
     */
    public function __construct($attributeSlotClass, $attributeGroupClass)
    {
        parent::__construct($attributeSlotClass);

        $this->attributeGroupClass = $attributeGroupClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', ResourceType::class, [
                'label'     => false,
                'class'     => $this->attributeGroupClass,
                'allow_new' => true,
                'attr'      => [
                    'widget_col' => 12,
                ],
            ])
            ->add('multiple', Type\CheckboxType::class, [
                'label'    => 'ekyna_product.attribute_set.field.multiple',
                'required' => false,
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
