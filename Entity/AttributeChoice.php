<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectTrait;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class AttributeChoice
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\AttributeChoiceTranslationInterface translate($locale = null, $create = false)
 */
class AttributeChoice extends RM\AbstractTranslatable implements Model\AttributeChoiceInterface
{
    use MediaSubjectTrait;
    use RM\SortableTrait;

    protected ?int                      $id        = null;
    protected ?Model\AttributeInterface $attribute = null;
    protected ?string                   $name      = null;
    protected ?string                   $color     = null;

    public function __toString(): string
    {
        return $this->name ?: 'New attribute choice';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttribute(): ?Model\AttributeInterface
    {
        return $this->attribute;
    }

    public function setAttribute(?Model\AttributeInterface $attribute): Model\AttributeChoiceInterface
    {
        if ($attribute === $this->attribute) {
            return $this;
        }

        if ($previous = $this->attribute) {
            $this->attribute = null;
            $previous->removeChoice($this);
        }

        if ($this->attribute = $attribute) {
            $attribute->addChoice($this);
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Model\AttributeChoiceInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): Model\AttributeChoiceInterface
    {
        $this->color = $color;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\AttributeChoiceInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    protected function getTranslationClass(): string
    {
        return AttributeChoiceTranslation::class;
    }
}
