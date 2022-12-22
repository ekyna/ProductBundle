<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface AttributeInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method AttributeTranslationInterface translate($locale = null, $create = false)
 */
interface AttributeInterface extends RM\SortableInterface, RM\TranslatableInterface
{
    public function getName(): ?string;

    public function setName(?string $name): AttributeInterface;

    public function getType(): ?string;

    public function setType(?string $type): AttributeInterface;

    public function getConfig(): array;

    public function setConfig(array $config): AttributeInterface;

    /**
     * Returns the (translated) title.
     */
    public function getTitle(): ?string;

    /**
     * Returns the (translated) title.
     */
    public function setTitle(?string $title): AttributeInterface;

    /**
     * @return Collection<int, AttributeChoiceInterface>
     */
    public function getChoices(): Collection;

    /**
     * @param Collection<int, AttributeChoiceInterface> $choices
     *
     * @internal
     */
    public function setChoices(Collection $choices): AttributeInterface;

    public function hasChoice(AttributeChoiceInterface $choice): bool;

    public function addChoice(AttributeChoiceInterface $choice): AttributeInterface;

    public function removeChoice(AttributeChoiceInterface $choice): AttributeInterface;
}
