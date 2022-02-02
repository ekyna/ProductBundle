<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class AttributeGroup
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\AttributeTranslationInterface translate(string $locale = null, bool $create = false)
 */
class Attribute extends RM\AbstractTranslatable implements Model\AttributeInterface
{
    use RM\SortableTrait;

    protected ?string $name   = null;
    protected ?string $type   = null;
    protected array   $config = [];
    /** @var Collection<Model\AttributeChoiceInterface> */
    protected Collection $choices;

    public function __construct()
    {
        parent::__construct();

        $this->choices = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: 'New attribute';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Model\AttributeInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Model\AttributeInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): Model\AttributeInterface
    {
        $this->config = $config;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\AttributeInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function hasChoice(Model\AttributeChoiceInterface $choice): bool
    {
        return $this->choices->contains($choice);
    }

    public function addChoice(Model\AttributeChoiceInterface $choice): Model\AttributeInterface
    {
        if (!$this->hasChoice($choice)) {
            $this->choices->add($choice);
            $choice->setAttribute($this);
        }

        return $this;
    }

    public function removeChoice(Model\AttributeChoiceInterface $choice): Model\AttributeInterface
    {
        if ($this->hasChoice($choice)) {
            $this->choices->removeElement($choice);
            $choice->setAttribute(null);
        }

        return $this;
    }

    public function setChoices(Collection $choices): Model\AttributeInterface
    {
        $this->choices = $choices;

        return $this;
    }

    protected function getTranslationClass(): string
    {
        return AttributeTranslation::class;
    }
}
