<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CoreBundle\Form\Type\ColorPickerType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AttributeChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeChoiceType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => AttributeChoiceTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('media', MediaChoiceType::class, [
                'label'    => 'ekyna_core.field.image',
                'types'    => [MediaTypes::IMAGE, MediaTypes::SVG],
                'required' => false,
            ])
            ->add('color', ColorPickerType::class, [
                'label'    => 'ekyna_core.field.color',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface $attribute */
            $attribute = $event->getData();
            $form = $event->getForm();

            $disabled = (null !== $attribute && $attribute->getId());

            $form->add('attribute', ResourceType::class, [
                'label'    => 'ekyna_product.attribute.label.singular',
                'resource' => 'ekyna_product.attribute',
                //'allow_new' => !$disabled,
                // TODO query_builder => filter attributes by type === 'select'
                'disabled' => $disabled,
            ]);
        });
    }
}
