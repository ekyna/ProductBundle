<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\AttributeSetChoiceType;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

use function Symfony\Component\Translation\t;

/**
 * Class VariableType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableType extends AbstractType
{
    private ResourceRepositoryInterface $attributeSetRepository;

    public function __construct(ResourceRepositoryInterface $attributeSetRepository)
    {
        $this->attributeSetRepository = $attributeSetRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('attributeSet', AttributeSetChoiceType::class, [
                'allow_new' => true,
                'attr'      => [
                    'class' => 'product-attribute-set',
                ],
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'save' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => t('button.save', [], 'EkynaUi'),
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                ],
            ]);

        // Post set data
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var ProductInterface $data */
            $data = $event->getData();

            $this->addVariantForm($event->getForm(), $data->getAttributeSet());
        });

        // Pre submit
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            /** @var AttributeSetInterface $attributeSet */
            $attributeSet = $this->attributeSetRepository->find(intval($data['attributeSet']));

            $this->addVariantForm($event->getForm(), $attributeSet);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class'        => ProductInterface::class,
                'attr'              => [
                    'class' => 'form-horizontal',
                ],
                'validation_groups' => ['convert_' . ProductTypes::TYPE_VARIABLE],
            ]);
    }

    /**
     * Adds the variant form.
     */
    private function addVariantForm(FormInterface $form, AttributeSetInterface $attributeSet = null): void
    {
        /** @var ProductInterface $variable */
        $variable = $form->getData();
        /** @var ProductInterface $variant */
        $variant = $variable->getVariants()->first();

        if (!empty($optionGroups = $variant->getOptionGroups()->toArray())) {
            $form->add('option_group_selection', OptionGroupChoiceType::class, [
                'optionGroups' => $optionGroups,
                'help'         => t('convert.simple_to_variable.option_group_choice', [], 'EkynaProduct'),
                'help_html'    => true,
            ]);
        }

        if (!empty($medias = $variant->getMedias()->toArray())) {
            $form->add('media_selection', MediaChoiceType::class, [
                'medias'    => $medias,
                'help'      => t('convert.simple_to_variable.media_choice', [], 'EkynaProduct'),
                'help_html' => true,
            ]);
        }

        if (!empty($tags = $variant->getTags()->toArray())) {
            $form->add('tag_selection', TagChoiceType::class, [
                'tags'      => $tags,
                'help'      => t('convert.simple_to_variable.tag_choice', [], 'EkynaProduct'),
                'help_html' => true,
            ]);
        }

        $form->add('variant', VariantType::class, [
            'property_path' => 'variants[0]',
            'attribute_set' => $attributeSet,
            'constraints'   => [
                new Constraints\Valid(),
            ],
        ]);
    }
}
