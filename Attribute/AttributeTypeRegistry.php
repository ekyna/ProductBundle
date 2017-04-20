<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;

use function array_key_exists;

/**
 * Class AttributeTypeRegistry
 * @package Ekyna\Bundle\ProductBundle\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeTypeRegistry implements AttributeTypeRegistryInterface
{
    /** @var array<string, Type\TypeInterface> */
    private array $types = [];

    public function registerType(Type\TypeInterface $type): self
    {
        $name = $type::getName();

        if ($this->hasType($name)) {
            throw new InvalidArgumentException("Attribute type '$name' is already registered.");
        }

        $this->types[$name] = $type;

        return $this;
    }

    public function hasType(string $name): bool
    {
        return array_key_exists($name, $this->types);
    }

    public function getType(string $name): Type\TypeInterface
    {
        if (!$this->hasType($name)) {
            throw new InvalidArgumentException("Attribute type '$name' is not registered.");
        }

        return $this->types[$name];
    }

    public function getTypes(): array
    {
        return $this->types;
    }
}
