<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface OptionInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OptionTranslationInterface translate($locale = null, $create = false)
 */
interface OptionInterface extends RM\TranslatableInterface, RM\SortableInterface, TaxableInterface
{
    public function getGroup(): ?OptionGroupInterface;

    public function setGroup(?OptionGroupInterface $group): OptionInterface;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): OptionInterface;

    /**
     * Returns whether option product's options should be added to the form (add to sale).
     */
    public function isCascade(): bool;

    /**
     * Sets whether option product's options should be added to the form (add to sale).
     */
    public function setCascade(bool $cascade): OptionInterface;

    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): OptionInterface;

    public function getReference(): ?string;

    public function setReference(?string $reference): OptionInterface;

    /**
     * Returns the (translated) title.
     */
    public function getTitle(): ?string;

    /**
     * Returns the (translated) title.
     */
    public function setTitle(?string $title): OptionInterface;

    public function getWeight(): ?Decimal;

    public function setWeight(?Decimal $weight): OptionInterface;

    public function getNetPrice(): ?Decimal;

    public function setNetPrice(?Decimal $netPrice): OptionInterface;
}
