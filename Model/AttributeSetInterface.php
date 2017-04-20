<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Comparable;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface AttributeSetInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeSetInterface extends ResourceInterface, Comparable
{
    public function getName(): ?string;

    public function setName(?string $name): AttributeSetInterface;

    /**
     * @return Collection<AttributeSlotInterface>
     */
    public function getSlots(): Collection;

    public function hasSlot(AttributeSlotInterface $slot): bool;

    public function addSlot(AttributeSlotInterface $slot): AttributeSetInterface;

    public function removeSlot(AttributeSlotInterface $slot): AttributeSetInterface;

    /**
     * @param Collection<AttributeSlotInterface> $slots
     *
     * @internal
     */
    public function setSlots(Collection $slots): AttributeSetInterface;

    /**
     * Returns whether this attribute set has at least one required slot.
     */
    public function hasRequiredSlot(): bool;

    /**
     * Returns whether this attribute set has at least one naming slot.
     */
    public function hasNamingSlot(): bool;
}
