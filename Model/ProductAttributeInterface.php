<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ProductAttributeInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductAttributeInterface extends ResourceInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): ProductAttributeInterface;

    public function getAttributeSlot(): ?AttributeSlotInterface;

    public function setAttributeSlot(?AttributeSlotInterface $slot): ProductAttributeInterface;

    /**
     * @return Collection<AttributeChoiceInterface>
     */
    public function getChoices(): Collection;

    public function hasChoice(AttributeChoiceInterface $choice): bool;

    public function addChoice(AttributeChoiceInterface $choice): ProductAttributeInterface;

    public function removeChoice(AttributeChoiceInterface $choice): ProductAttributeInterface;

    public function getValue(): string;

    public function setValue(?string $value): ProductAttributeInterface;

    /**
     * Returns whether the product attribute has no value and no choices.
     */
    public function isEmpty(): bool;
}
