<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeSlotInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class ProductAttribute
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttribute extends AbstractResource implements ProductAttributeInterface
{
    protected ?ProductInterface       $product       = null;
    protected ?AttributeSlotInterface $attributeSlot = null;
    /** @var Collection<int, AttributeChoiceInterface> */
    protected Collection $choices;
    protected ?string    $value = null;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    public function __clone()
    {
        parent::__clone();

        $this->product = null;
    }

    public function onCopy(CopierInterface $copier): void
    {
        $copier->copyCollection($this, 'choices', false);
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): ProductAttributeInterface
    {
        if ($this->product === $product) {
            return $this;
        }

        if ($previous = $this->product) {
            $this->product = null;
            $previous->removeAttribute($this);
        }

        if ($this->product = $product) {
            $this->product->addAttribute($this);
        }

        return $this;
    }

    public function getAttributeSlot(): AttributeSlotInterface
    {
        return $this->attributeSlot;
    }

    public function setAttributeSlot(?AttributeSlotInterface $slot): ProductAttributeInterface
    {
        $this->attributeSlot = $slot;

        return $this;
    }

    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function hasChoice(AttributeChoiceInterface $choice): bool
    {
        return $this->choices->contains($choice);
    }

    public function addChoice(AttributeChoiceInterface $choice): ProductAttributeInterface
    {
        if (!$this->hasChoice($choice)) {
            $this->choices->add($choice);
        }

        return $this;
    }

    public function removeChoice(AttributeChoiceInterface $choice): ProductAttributeInterface
    {
        if ($this->hasChoice($choice)) {
            $this->choices->removeElement($choice);
        }

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): ProductAttributeInterface
    {
        $this->value = $value;

        return $this;
    }

    public function isEmpty(): bool
    {
        return is_null($this->value) && 0 === $this->choices->count();
    }
}
