<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttributeSetChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSetChoiceType extends AbstractType
{
    /**
     * @var string
     */
    private $attributeSetClass;


    /**
     * Constructor.
     *
     * @param string $attributeSetClass
     */
    public function __construct($attributeSetClass)
    {
        $this->attributeSetClass = $attributeSetClass;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'       => 'ekyna_product.attribute_set.label.singular',
            'class'       => $this->attributeSetClass,
            'placeholder' => 'ekyna_core.value.choose',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ResourceType::class;
    }
}
