<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Form\DataTransformer\ProductAttributesTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributesType extends AbstractType
{
    private AttributeTypeRegistryInterface $typeRegistry;
    private string $productAttributeClass;

    public function __construct(AttributeTypeRegistryInterface $typeRegistry, string $productAttributeClass)
    {
        $this->typeRegistry = $typeRegistry;
        $this->productAttributeClass = $productAttributeClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new ProductAttributesTransformer($this->productAttributeClass, $options['attribute_set'])
        );

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $form->getNormData();

            /** @var Model\ProductAttributeInterface $productAttribute */
            foreach ($data as $index => $productAttribute) {
                $slot = $productAttribute->getAttributeSlot();
                $attribute = $slot->getAttribute();
                $attributeType = $this->typeRegistry->getType($attribute->getType());

                $form->add($index, $attributeType->getFormType(), [
                    'label'      => $attribute->getName(),
                    'data_class' => $this->productAttributeClass,
                    'attribute'  => $attribute,
                    'required'   => $slot->isRequired(),
                ]);
            }
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = array_merge($view->vars['attr'], [
            'class'          => 'product-attributes',
            'data-set-field' => '.product-attribute-set',
            'data-parent-name' => $view->parent->vars['full_name'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'attribute_set' => null,
                'required'      => function (Options $options) {
                    /** @var Model\AttributeSetInterface $set */
                    $attributeSet = $options['attribute_set'];

                    return $attributeSet && $attributeSet->hasRequiredSlot();
                },
            ])
            ->setAllowedTypes('attribute_set', [Model\AttributeSetInterface::class, 'null']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_product_attributes';
    }
}
