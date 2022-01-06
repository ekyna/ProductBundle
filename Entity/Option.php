<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Pricing\Model\TaxableTrait;
use Ekyna\Component\Resource\Model as RM;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Class Option
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\OptionTranslationInterface translate(string $locale = null, bool $create = false)
 *
 * @TODO    Rename to 'OptionValue' or 'OptionChoice'
 */
class Option extends RM\AbstractTranslatable implements Model\OptionInterface, GroupSequenceProviderInterface
{
    use RM\SortableTrait;
    use TaxableTrait;

    protected ?int                        $id          = null;
    protected ?Model\OptionGroupInterface $group       = null;
    protected ?Model\ProductInterface     $product     = null;
    protected bool                        $cascade     = false;
    protected ?string                     $designation = null;
    protected ?string                     $reference   = null;
    protected ?Decimal                    $weight      = null;
    protected ?Decimal                    $netPrice    = null;

    public function __clone()
    {
        parent::__clone();

        $this->group = null;
    }

    public function __toString(): string
    {
        return $this->designation ?: 'New option';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): ?Model\OptionGroupInterface
    {
        return $this->group;
    }

    public function setGroup(?Model\OptionGroupInterface $group): Model\OptionInterface
    {
        if ($this->group === $group) {
            return $this;
        }

        if ($previous = $this->group) {
            $this->group = null;
            $previous->removeOption($this);
        }

        if ($this->group = $group) {
            $this->group->addOption($this);
        }

        return $this;
    }

    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?Model\ProductInterface $product): Model\OptionInterface
    {
        $this->product = $product;

        return $this;
    }

    public function isCascade(): bool
    {
        return $this->cascade;
    }

    public function setCascade(bool $cascade): Model\OptionInterface
    {
        $this->cascade = $cascade;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): Model\OptionInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): Model\OptionInterface
    {
        $this->reference = $reference;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\OptionInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getWeight(): ?Decimal
    {
        return $this->weight;
    }

    public function setWeight(?Decimal $weight): Model\OptionInterface
    {
        $this->weight = $weight;

        return $this;
    }

    public function getNetPrice(): ?Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(?Decimal $netPrice): Model\OptionInterface
    {
        $this->netPrice = $netPrice;

        return $this;
    }

    /**
     * Returns the mode (for the option form type).
     *
     * @return string
     */
    public function getMode(): string
    {
        return null !== $this->product ? 'product' : 'data';
    }

    /**
     * Fake setter (for the option form type).
     *
     * @param $mode
     */
    public function setMode($mode): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getGroupSequence()
    {
        $groups = ['Option'];

        if (null !== $this->product) {
            $groups[] = 'product';
        } else {
            $groups[] = 'data';
        }

        return $groups;
    }

    protected function getTranslationClass(): string
    {
        return OptionTranslation::class;
    }
}
