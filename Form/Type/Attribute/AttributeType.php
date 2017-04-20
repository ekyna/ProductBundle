<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Attribute\Type\TypeInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class AttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeType extends AbstractResourceType
{
    private AttributeTypeRegistryInterface $typeRegistry;

    public function __construct(AttributeTypeRegistryInterface $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('type', Type\ChoiceType::class, [
                'label'        => t('field.type', [], 'EkynaUi'),
                'choices'      => $this->typeRegistry->getTypes(),
                'choice_label' => function (TypeInterface $type) {
                    return $type::getName();
                },
                'disabled'     => true,
                'select2'      => false,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => AttributeTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var AttributeInterface $attribute */
                $attribute = $event->getData();

                $type = $this->typeRegistry->getType($attribute->getType());

                if (null !== $formType = $type->getConfigType()) {
                    $event->getForm()->add('config', $formType);
                }
            });
    }
}
