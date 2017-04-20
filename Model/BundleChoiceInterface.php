<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface BundleChoiceInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleChoiceInterface extends ResourceInterface, SortableInterface
{
    public function getSlot(): ?BundleSlotInterface;

    public function setSlot(?BundleSlotInterface $slot): BundleChoiceInterface;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): BundleChoiceInterface;

    public function getMinQuantity(): Decimal;

    public function setMinQuantity(Decimal $quantity): BundleChoiceInterface;

    public function getMaxQuantity(): Decimal;

    public function setMaxQuantity(Decimal $quantity): BundleChoiceInterface;

    /**
     * Returns the excluded option groups ids.
     *
     * @return array<int>
     */
    public function getExcludedOptionGroups(): array;

    /**
     * Sets the excluded option groups ids.
     *
     * @param array<int> $ids
     */
    public function setExcludedOptionGroups(array $ids): BundleChoiceInterface;

    public function getNetPrice(): ?Decimal;

    public function setNetPrice(?Decimal $price): BundleChoiceInterface;

    public function isHidden(): bool;

    public function setHidden(bool $hidden): BundleChoiceInterface;

    /**
     * @return Collection<BundleChoiceRuleInterface>
     */
    public function getRules(): Collection;

    /**
     * Returns whether the bundle choice has the rule or not.
     */
    public function hasRule(BundleChoiceRuleInterface $rule): bool;

    public function addRule(BundleChoiceRuleInterface $rule): BundleChoiceInterface;

    public function removeRule(BundleChoiceRuleInterface $rule): BundleChoiceInterface;

    /**
     * @param Collection<BundleChoiceRuleInterface> $rules
     *
     * @internal
     */
    public function setRules(Collection $rules): BundleChoiceInterface;

    /**
     * Returns whether to exclude images (from parent gallery).
     */
    public function isExcludeImages(): bool;

    /**
     * Sets whether to exclude images (from parent gallery).
     */
    public function setExcludeImages(bool $exclude): BundleChoiceInterface;
}
