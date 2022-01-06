<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Resource\Copier\CopyInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface OptionGroupInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OptionGroupTranslationInterface translate(string $locale = null, bool $create = false)
 */
interface OptionGroupInterface extends RM\TranslatableInterface, RM\SortableInterface, CopyInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): OptionGroupInterface;

    public function getName(): ?string;

    public function setName(?string $name): OptionGroupInterface;

    /**
     * Returns the (translated) title.
     */
    public function getTitle(): ?string;

    /**
     * Returns the (translated) title.
     */
    public function setTitle(?string $title): OptionGroupInterface;

    public function isRequired(): bool;

    public function setRequired(bool $required): OptionGroupInterface;

    /**
     * Returns whether to display variant's full titles.
     */
    public function isFullTitle(): bool;

    /**
     * Sets the whether to display variant's full titles.
     */
    public function setFullTitle(bool $full): OptionGroupInterface;

    /**
     * @return Collection<OptionInterface>
     */
    public function getOptions(): Collection;

    /**
     * Returns whether the group has the option or not.
     */
    public function hasOption(OptionInterface $option): bool;

    public function addOption(OptionInterface $option): OptionGroupInterface;

    public function removeOption(OptionInterface $option): OptionGroupInterface;

    /**
     * @param Collection<OptionInterface> $options
     *
     * @internal
     */
    public function setOptions(Collection $options): OptionGroupInterface;
}
