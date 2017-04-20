<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class AttributeSlot
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSlot implements Model\AttributeSlotInterface
{
    use SortableTrait;

    protected ?int                         $id        = null;
    protected ?Model\AttributeSetInterface $set       = null;
    protected ?Model\AttributeInterface    $attribute = null;
    protected bool                         $required  = false;
    protected bool                         $naming    = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSet(): ?Model\AttributeSetInterface
    {
        return $this->set;
    }

    public function setSet(Model\AttributeSetInterface $set = null): Model\AttributeSlotInterface
    {
        if ($this->set === $set) {
            return $this;
        }

        if ($previous = $this->set) {
            $this->set = null;
            $previous->removeSlot($this);
        }

        if ($this->set = $set) {
            $this->set->addSlot($this);
        }

        return $this;
    }

    public function getAttribute(): ?Model\AttributeInterface
    {
        return $this->attribute;
    }

    public function setAttribute(?Model\AttributeInterface $attribute): Model\AttributeSlotInterface
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): Model\AttributeSlotInterface
    {
        $this->required = $required;

        return $this;
    }

    public function isNaming(): bool
    {
        return $this->naming;
    }

    public function setNaming(bool $naming): Model\AttributeSlotInterface
    {
        $this->naming = $naming;

        return $this;
    }
}
