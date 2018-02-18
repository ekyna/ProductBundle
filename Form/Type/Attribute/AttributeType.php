<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class AttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeType extends ResourceFormType
{
    /**
     * @var AttributeTypeRegistryInterface
     */
    private $typeRegistry;


    /**
     * Constructor.
     *
     * @param string                         $class
     * @param AttributeTypeRegistryInterface $typeRegistry
     */
    public function __construct($class, AttributeTypeRegistryInterface $typeRegistry)
    {
        parent::__construct($class);

        $this->typeRegistry = $typeRegistry;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('type', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.type',
                'choices'  => $this->typeRegistry->getChoices(),
                'disabled' => true,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => AttributeTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /** @var AttributeInterface $attribute */
                $attribute = $event->getData();

                $type = $this->typeRegistry->getType($attribute->getType());

                if (null !== $formType = $type->getConfigType()) {
                    $event->getForm()->add('config', $formType);
                }
            });
    }
}
