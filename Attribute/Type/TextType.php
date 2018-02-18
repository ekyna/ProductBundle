<?php

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type\TextAttributeType;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TextType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TextType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function getConstraints(ProductAttributeInterface $productAttribute)
    {
        if ($productAttribute->getAttributeSlot()->isRequired()) {
            return [
                'value' => [
                    new NotBlank(),
                ],
            ];
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function getFormType()
    {
        return TextAttributeType::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return 'ekyna_product.attribute.type.text';
    }
}