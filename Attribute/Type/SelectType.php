<?php

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Form\Type\Attribute as Form;
use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Class SelectType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SelectType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function render(ProductAttributeInterface $productAttribute, $locale = null)
    {
        $labels = array_map(function(AttributeChoiceInterface $choice) use ($locale) {
            return $choice->translate($locale)->getTitle();
        }, $productAttribute->getChoices()->toArray());

        if (!empty($labels)) {
            return implode(' ', $labels);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasChoices()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getConstraints(ProductAttributeInterface $productAttribute)
    {
        if ($productAttribute->getAttributeSlot()->isRequired()) {
            return [
                'choices' => [
                    new Count([
                        'min' => 1,
                    ]),
                ],
            ];
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConfigShowFields(AttributeInterface $attribute)
    {
        $config = $attribute->getConfig();

        return [
            [
                'value'   => $config['multiple'],
                'type'    => 'boolean',
                'options' => [
                    'label' => 'ekyna_product.attribute.config.multiple',
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getConfigDefaults()
    {
        return [
            'multiple' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getConfigType()
    {
        return Form\Config\SelectConfigType::class;
    }

    /**
     * @inheritDoc
     */
    public function getFormType()
    {
        return Form\Type\SelectAttributeType::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return 'ekyna_product.attribute.type.select';
    }
}