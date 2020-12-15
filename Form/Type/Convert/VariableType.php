<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\AttributeSetChoiceType;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

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
     * @param ResourceHelper              $resourceHelper
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
                            'label'        => 'ekyna_core.button.save',
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
            $attributeSet = $this->attributeSetRepository->find($data['attributeSet']);

            $this->addVariantForm($event->getForm(), $attributeSet);
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
                'validation_groups' => ['convert_' . ProductTypes::TYPE_VARIABLE],
            ]);
    }

    /**
     * Adds the variant form.
     *
     * @param FormInterface              $form
     * @param AttributeSetInterface|null $attributeSet
     */
    private function addVariantForm(FormInterface $form, AttributeSetInterface $attributeSet = null)
    {
        /** @var ProductInterface $variable */
        $variable = $form->getData();
        /** @var ProductInterface $variant */
        $variant = $variable->getVariants()->first();

        if (!empty($optionGroups = $variant->getOptionGroups()->toArray())) {
            $form->add('option_group_selection', OptionGroupChoiceType::class, [
                'optionGroups' => $optionGroups,
                'attr'         => [
                    'help_text' => 'ekyna_product.convert.simple_to_variable.option_group_choice',
                ],
            ]);
        }

        if (!empty($medias = $variant->getMedias()->toArray())) {
            $form->add('media_selection', MediaChoiceType::class, [
                'medias' => $medias,
                'attr'   => [
                    'help_text' => 'ekyna_product.convert.simple_to_variable.media_choice',
                ],
            ]);
        }

        if (!empty($tags = $variant->getTags()->toArray())) {
            $form->add('tag_selection', TagChoiceType::class, [
                'tags' => $tags,
                'attr' => [
                    'help_text' => 'ekyna_product.convert.simple_to_variable.tag_choice',
                ],
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
