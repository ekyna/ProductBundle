<?php

namespace Ekyna\Bundle\ProductBundle\Attribute;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;

/**
 * Class AttributeTypeRegistry
 * @package Ekyna\Bundle\ProductBundle\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeTypeRegistry implements AttributeTypeRegistryInterface
{
    /**
     * @var Type\TypeInterface[]
     */
    private $types;

    /**
     * @var array
     */
    private $choices;


    /**
     * Constructor.
     *
     * @param Type\TypeInterface[] $types
     */
    public function __construct(array $types = [])
    {
        $this->types = [];

        foreach ($types as $name => $type) {
            $this->registerType($name, $type);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerType($name, Type\TypeInterface $type)
    {
        if ($this->hasType($name)) {
            throw new InvalidArgumentException("Attribute type '$name' is already registered.");
        }

        $this->types[$name] = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasType($name)
    {
        return isset($this->types[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new InvalidArgumentException("Attribute type '$name' is not registered.");
        }

        return $this->types[$name];
    }

    /**
     * @inheritdoc
     */
    public function getChoices()
    {
        if (null !== $this->choices) {
            return $this->choices;
        }

        $this->choices = [];

        foreach ($this->types as $name => $type) {
            $this->choices[$type->getLabel()] = $name;
        }

        return $this->choices;
    }

    /**
     * @inheritdoc
     */
    public function getTypes()
    {
        return $this->types;
    }
}