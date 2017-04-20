<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\ColorPickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class AttributeChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeChoiceType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => AttributeChoiceTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('media', MediaChoiceType::class, [
                'label'    => t('field.image', [], 'EkynaUi'),
                'types'    => [MediaTypes::IMAGE, MediaTypes::SVG],
                'required' => false,
            ])
            ->add('color', ColorPickerType::class, [
                'label'    => t('field.color', [], 'EkynaUi'),
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var AttributeChoiceInterface $attribute */
            $attribute = $event->getData();
            $form = $event->getForm();

            $disabled = (null !== $attribute && $attribute->getId());

            $form->add('attribute', ResourceChoiceType::class, [
                'label'    => t('attribute.label.singular', [], 'EkynaProduct'),
                'resource' => 'ekyna_product.attribute',
                //'allow_new' => !$disabled,
                // TODO query_builder => filter attributes by type === 'select'
                'disabled' => $disabled,
            ]);
        });
    }
}
