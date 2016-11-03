<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $attributeGroupClass;


    /**
     * Constructor.
     *
     * @param string $attributeClass
     * @param string $attributeGroupClass
     */
    public function __construct($attributeClass, $attributeGroupClass)
    {
        parent::__construct($attributeClass);

        $this->attributeGroupClass = $attributeGroupClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'ekyna_core.field.name',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeInterface $attribute */
            $attribute = $event->getData();
            $form = $event->getForm();

            $disabled = (null !== $attribute && $attribute->getId());

            $form->add('group', ResourceType::class, [
                'label'     => 'ekyna_product.attribute_group.label.singular',
                'class'     => $this->attributeGroupClass,
                'allow_new' => !$disabled,
                'disabled'  => $disabled,
            ]);
        });
    }
}
