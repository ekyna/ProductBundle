<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Form\DataTransformer\ProductAttributesTransformer;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributesType extends AbstractType
{
    /**
     * @var AttributeTypeRegistryInterface
     */
    private $typeRegistry;

    /**
     * @var string
     */
    private $productAttributeClass;


    /**
     * Constructor.
     *
     * @param AttributeTypeRegistryInterface $typeRegistry
     * @param string                         $productAttributeClass
     */
    public function __construct(AttributeTypeRegistryInterface $typeRegistry, $productAttributeClass)
    {
        $this->typeRegistry = $typeRegistry;
        $this->productAttributeClass = $productAttributeClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new ProductAttributesTransformer($this->productAttributeClass, $options['attribute_set'])
        );

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
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

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('attribute_set')
            ->setAllowedTypes('attribute_set', [Model\AttributeSetInterface::class, 'null']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_product_attributes';
    }
}
