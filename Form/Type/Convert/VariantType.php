<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\ProductBundle\Form\Type\ProductAttributesType;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VariantType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attributeSet = $options['attribute_set'];

        $builder->add('attributes', ProductAttributesType::class, [
            'label'         => 'ekyna_product.attribute.label.plural',
            'attribute_set' => $attributeSet,
            'required'      => $attributeSet->hasRequiredSlot(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'attribute_set'     => null,
                'data_class'        => ProductInterface::class,
                'validation_groups' => ['Default', ProductTypes::TYPE_VARIANT],
            ])
            ->setAllowedTypes('attribute_set', AttributeSetInterface::class);
    }
}
