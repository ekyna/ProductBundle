<?php

declare(strict_types=1);

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
    public function render(ProductAttributeInterface $productAttribute, string $locale = null): ?string
    {
        if (!empty($value = $productAttribute->getValue())) {
            return (string) $value;
        }

        return null;
    }

    public function hasChoices(): bool
    {
        return false;
    }

    public function getConstraints(ProductAttributeInterface $productAttribute): array
    {
        return [];
    }

    public function getConfigShowFields(AttributeInterface $attribute): array
    {
        return [];
    }

    public function getConfigDefaults(): array
    {
        return [];
    }

    public function getConfigType(): ?string
    {
        return null;
    }
}
