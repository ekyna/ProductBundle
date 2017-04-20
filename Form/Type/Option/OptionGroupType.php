<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Option;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class OptionGroupType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Option
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => OptionGroupTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('required', Type\CheckboxType::class, [
                'label'    => t('field.required', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('fullTitle', Type\CheckboxType::class, [
                'label'    => t('option_group.field.full_title', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('options', CollectionType::class, [
                'label'           => t('option.label.plural', [], 'EkynaProduct'),
                'prototype_name'  => '__option__',
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => true,
                'entry_type'      => OptionType::class,
                'add_button_text' => t('option.button.add', [], 'EkynaProduct'),
            ])
            ->add('position', CollectionPositionType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_option_group';
    }
}
