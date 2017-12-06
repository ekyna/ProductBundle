<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleSlotType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotType extends ResourceFormType
{
    /**
     * @var string
     */
    private $bundleChoiceClass;


    /**
     * Constructor.
     *
     * @param string $bundleSlotClass
     * @param string $bundleChoiceClass
     */
    public function __construct($bundleSlotClass, $bundleChoiceClass)
    {
        parent::__construct($bundleSlotClass);

        $this->bundleChoiceClass = $bundleChoiceClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
                    'label'    => 'ekyna_core.field.image',
                    'required' => false,
                    'types'    => [MediaTypes::IMAGE, MediaTypes::SVG],
                ])
                ->add('required', CheckboxType::class, [
                    'label'    => 'ekyna_core.field.required',
                    'required' => false,
                    'attr' => [
                        'align_with_widget' => true,
                    ]
                ]);
        }

        $builder
            ->add('choices', BundleChoicesType::class, [
                'configurable' => $options['configurable'],
                'choice_class' => $this->bundleChoiceClass,
            ])
            ->add('position', CollectionPositionType::class);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['configurable'] = $options['configurable'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'configurable' => false,
            ])
            ->setAllowedTypes('configurable', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_bundle_slot';
    }
}
