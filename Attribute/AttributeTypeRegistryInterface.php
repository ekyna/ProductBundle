<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute;

/**
 * Interface AttributeTypeRegistryInterface
 * @package Ekyna\Bundle\ProductBundle\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeTypeRegistryInterface
{
    /**
     * Registers the given attribute type.
     */
    public function registerType(Type\TypeInterface $type): self;

    /**
     * Returns whether a type is registered for the given name.
     */
    public function hasType(string $name): bool;

    /**
     * Returns the attribute type for the given name.
     *
     * @param string $name
     *
     * @return Type\TypeInterface
     */
    public function getType(string $name): Type\TypeInterface;

    /**
     * Returns all the attribute types.
     *
     * @return array<string, Type\TypeInterface>
     */
    public function getTypes(): array;
}
