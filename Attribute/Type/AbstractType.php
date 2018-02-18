<?php

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;

/**
 * Class AbstractType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function render(ProductAttributeInterface $productAttribute, $locale = null)
    {
        if (!empty($value = $productAttribute->getValue())) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasChoices()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getConstraints(ProductAttributeInterface $productAttribute)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConfigShowFields(AttributeInterface $attribute)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConfigDefaults()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConfigType()
    {
        return null;
    }
}
