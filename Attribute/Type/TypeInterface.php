<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Interface TypeInterface
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TypeInterface
{
    public const TYPE_TAG = 'ekyna_product.attribute_type';

    /**
     * Renders the product attribute.
     */
    public function render(ProductAttributeInterface $productAttribute, string $locale = null): ?string;

    /**
     * Returns whether this type works with attribute choices.
     */
    public function hasChoices(): bool;

    /**
     * Returns the validation constraints.
     */
    public function getConstraints(ProductAttributeInterface $productAttribute): array;

    /**
     * Returns the config show fields.
     */
    public function getConfigShowFields(AttributeInterface $attribute): array;

    /**
     * Returns configuration defaults.
     */
    public function getConfigDefaults(): array;

    /**
     * Returns the configuration form type class.
     */
    public function getConfigType(): ?string;

    /**
     * Returns the product attribute form type class.
     */
    public function getFormType(): ?string;

    public function getLabel(): TranslatableInterface;

    public static function getName(): string;
}
