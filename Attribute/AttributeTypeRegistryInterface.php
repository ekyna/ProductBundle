<?php

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
     *
     * @param string             $name
     * @param Type\TypeInterface $type
     *
     * @return AttributeTypeRegistry
     */
    public function registerType($name, Type\TypeInterface $type);

    /**
     * Returns whether a type is registered for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasType($name);

    /**
     * Returns the attribute type for the given name.
     *
     * @param string $name
     *
     * @return Type\TypeInterface
     */
    public function getType($name);

    /**
     * Returns the type choices.
     *
     * @return array
     */
    public function getChoices();

    /**
     * Returns all the attribute types.
     *
     * @return Type\TypeInterface[]
     */
    public function getTypes();
}