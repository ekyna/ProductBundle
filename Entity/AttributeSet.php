<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class AttributeSet
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSet implements Model\AttributeSetInterface
{
    protected ?int       $id   = null;
    protected ?string    $name = null;
    protected Collection $slots;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: 'New attribute set';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Model\AttributeSetInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getSlots(): Collection
    {
        return $this->slots;
    }

    public function hasSlot(Model\AttributeSlotInterface $slot): bool
    {
        return $this->slots->contains($slot);
    }

    public function addSlot(Model\AttributeSlotInterface $slot): Model\AttributeSetInterface
    {
        if (!$this->hasSlot($slot)) {
            $this->slots->add($slot);
            $slot->setSet($this);
        }

        return $this;
    }

    public function removeSlot(Model\AttributeSlotInterface $slot): Model\AttributeSetInterface
    {
        if ($this->hasSlot($slot)) {
            $this->slots->removeElement($slot);
            $slot->setSet(null);
        }

        return $this;
    }

    public function setSlots(Collection $slots): Model\AttributeSetInterface
    {
        $this->slots = $slots;

        return $this;
    }

    public function hasRequiredSlot(): bool
    {
        foreach ($this->slots as $slot) {
            if ($slot->isRequired()) {
                return true;
            }
        }

        return false;
    }

    public function hasNamingSlot(): bool
    {
        foreach ($this->slots as $slot) {
            if ($slot->isNaming()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @see https://github.com/Atlantic18/DoctrineExtensions/issues/1726
     */
    public function compareTo($other)
    {
        if ($other instanceof Model\AttributeSetInterface) {
            if ($this->id && $other->getId()) {
                return $this->id - $other->getId();
            }
            if ($this->id && !$other->getId()) {
                return 1;
            }
            if (!$this->id && $other->getId()) {
                return -1;
            }
        }

        return 0;
    }
}
