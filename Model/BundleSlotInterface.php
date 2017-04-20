<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Resource\Model\SortableInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface BundleSlotInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method BundleSlotTranslationInterface translate($locale = null, $create = false)
 */
interface BundleSlotInterface extends TranslatableInterface, MediaSubjectInterface, SortableInterface
{
    public function getBundle(): ?ProductInterface;

    public function setBundle(?ProductInterface $bundle): BundleSlotInterface;

    public function getTitle(): ?string;

    public function setTitle(?string $title): BundleSlotInterface;

    public function getDescription(): ?string;

    public function setDescription(?string $description): BundleSlotInterface;

    /**
     * @return Collection<BundleChoiceInterface>
     */
    public function getChoices(): Collection;

    public function hasChoice(BundleChoiceInterface $choice): bool;

    public function addChoice(BundleChoiceInterface $choice): BundleSlotInterface;

    public function removeChoice(BundleChoiceInterface $choice): BundleSlotInterface;

    /**
     * @param Collection<BundleChoiceInterface> $choices
     */
    public function setChoices(Collection $choices): BundleSlotInterface;

    public function isRequired(): bool;

    public function setRequired(bool $required): BundleSlotInterface;

    /**
     * @return Collection<BundleSlotRuleInterface>
     */
    public function getRules(): Collection;

    public function hasRule(BundleSlotRuleInterface $rule): bool;

    public function addRule(BundleSlotRuleInterface $rule): BundleSlotInterface;

    public function removeRule(BundleSlotRuleInterface $rule): BundleSlotInterface;

    /**
     * @param Collection<BundleSlotRuleInterface> $rules
     *
     * @internal
     */
    public function setRules(Collection $rules): BundleSlotInterface;
}
