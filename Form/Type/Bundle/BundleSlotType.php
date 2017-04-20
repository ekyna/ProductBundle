<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BundleSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotType extends AbstractResourceType
{
    private string $bundleChoiceClass;

    public function __construct(string $bundleChoiceClass)
    {
        $this->bundleChoiceClass = $bundleChoiceClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['configurable']) {
            // Bundle type : ensure one and only one choice.
            $bundleSlotClass = $this->dataClass;
            $bundleChoiceClass = $this->bundleChoiceClass;

            $builder
                ->addModelTransformer(new CallbackTransformer(
                    function ($slot) use ($bundleSlotClass, $bundleChoiceClass) {
                        if (null === $slot) {
                            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
                            $slot = new $bundleSlotClass();
                        }

                        $choices = $slot->getChoices();
                        if ($choices->isEmpty()) {
                            $slot->addChoice(new $bundleChoiceClass);
                        }

                        return $slot;
                    },
                    function ($data) {
                        return $data;
                    }
                ));
        } else {
            $builder
                ->add('translations', TranslationsFormsType::class, [
                    'form_type'      => BundleSlotTranslationType::class,
                    'label'          => false,
                    'error_bubbling' => false,
                ])
                ->add('media', MediaChoiceType::class, [
                    'label'    => t('field.image', [], 'EkynaUi'),
                    'required' => false,
                    'types'    => [MediaTypes::IMAGE, MediaTypes::SVG],
                ])
                ->add('required', CheckboxType::class, [
                    'label'    => t('field.required', [], 'EkynaUi'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('rules', BundleRulesType::class, [
                    'entry_type'     => BundleSlotRuleType::class,
                    'prototype_name' => '__slot_rule__',
                ]);
        }

        $builder
            ->add('choices', BundleChoicesType::class, [
                'configurable' => $options['configurable'],
                'choice_class' => $this->bundleChoiceClass,
            ])
            ->add('position', CollectionPositionType::class);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['configurable'] = $options['configurable'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'configurable' => false,
            ])
            ->setAllowedTypes('configurable', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_bundle_slot';
    }
}
