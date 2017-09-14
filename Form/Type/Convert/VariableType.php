<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\AttributeSetChoiceType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductTranslationType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class VariableType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableType extends AbstractType
{
    /**
     * @var ResourceHelper
     */
    private $resourceHelper;

    /**
     * @var ResourceRepositoryInterface
     */
    private $attributeSetRepository;


    /**
     * Constructor.
     *
     * @param ResourceHelper $resourceHelper
     * @param ResourceRepositoryInterface $attributeSetRepository
     */
    public function __construct(ResourceHelper $resourceHelper, ResourceRepositoryInterface $attributeSetRepository)
    {
        $this->resourceHelper = $resourceHelper;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Type\TextType::class, [
                'label' => 'ekyna_core.field.designation',
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => ProductTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
                'attr'           => [
                    'label_col'  => 0,
                    'widget_col' => 12,
                ],
            ])
            ->add('seo', SeoType::class)
            ->add('attributeSet', AttributeSetChoiceType::class, [
                'allow_new' => true,
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'save' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
                            'label'        => 'ekyna_core.button.save',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    /*'cancel' => [
                        'type'    => Type\ButtonType::class,
                        'options' => [
                            'label'        => 'ekyna_core.button.cancel',
                            'button_class' => 'default',
                            'as_link'      => true,
                            'attr'         => [
                                'class' => 'form-cancel-btn',
                                'icon'  => 'remove',
                                'href'  => $this->resourceHelper->generateResourcePath($variant),
                            ],
                        ],
                    ],*/
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            $attributeSet = $this->attributeSetRepository->find($data['attributeSet']);
            if (null !== $attributeSet) {
                $form->add('variant', VariantType::class, [
                    'property_path' => 'variants[0]',
                    'attribute_set' => $attributeSet,
                    'constraints' => [
                        new Valid()
                    ]
                ]);

                /** @var ProductInterface $variable */
                $variable = $form->getData();
                /** @var ProductInterface $variant */
                $variant = $variable->getVariants()->first();

                if (!empty($optionGroups = $variant->getOptionGroups()->toArray())) {
                    $form->add('option_group_selection', OptionGroupChoiceType::class, [
                        'optionGroups' => $optionGroups,
                    ]);
                }

                if (!empty($medias = $variant->getMedias()->toArray())) {
                    $form->add('media_selection', MediaChoiceType::class, [
                        'medias' => $medias,
                    ]);
                }

                if (!empty($tags = $variant->getTags()->toArray())) {
                    $form->add('tag_selection', TagChoiceType::class, [
                        'tags' => $tags,
                    ]);
                }
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'        => ProductInterface::class,
                'attr'              => [
                    'class' => 'form-horizontal',
                ],
                'validation_groups' => ['Default', ProductTypes::TYPE_VARIABLE],
            ]);
    }
}
