<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Exception\InvalidArgumentException;

/**
 * Class ProductAttributesSlot
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * This is not an entity : just used in ProductAttributeTransformer (form usage)
 */
class ProductAttributesSlot
{
    /**
     * @var AttributeGroupInterface
     */
    private $group;

    /**
     * @var ArrayCollection
     */
    private $attributes;


    /**
     * Constructor.
     *
     * @param AttributeGroupInterface $group
     * @param array                   $attributes
     */
    public function __construct(AttributeGroupInterface $group, array $attributes = [])
    {
        $this->group = $group;
        $this->attributes = new ArrayCollection($attributes);
    }

    /**
     * Returns the group.
     *
     * @return AttributeGroupInterface
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Returns the attributes (as array).
     *
     * @return ArrayCollection|AttributeInterface[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Adds the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return ProductAttributesSlot
     */
    public function addAttribute(AttributeInterface $attribute)
    {
        $this->checkAttributeGroup($attribute);

        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * Removes the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return ProductAttributesSlot
     */
    public function removeAttribute(AttributeInterface $attribute)
    {
        $this->checkAttributeGroup($attribute);

        if ($this->attributes->contains($attribute)) {
            $this->attributes->removeElement($attribute);
        }

        return $this;
    }

    /**
     * Checks that the attribute group matches the slot one.
     *
     * @param AttributeInterface $attribute
     *
     * @throws InvalidArgumentException
     */
    private function checkAttributeGroup(AttributeInterface $attribute)
    {
        if ($attribute->getGroup() !== $this->group) {
            throw new InvalidArgumentException('This attribute does not belongs to this slot group.');
        }
    }
}
