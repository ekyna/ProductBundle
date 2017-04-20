<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Class InventoryContext
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryContext
{
    private ?int    $brand         = null;
    private ?int    $supplier      = null;
    private ?string $reference     = null;
    private ?string $designation   = null;
    private ?string $geocode       = null;
    private ?bool   $visible       = null;
    private ?bool   $quoteOnly     = null;
    private ?bool   $endOfLife     = null;
    private ?string $mode          = null;
    private ?string $state         = null;
    private ?bool   $bookmark      = null;
    private ?string $referenceCode = null;
    private string  $profile       = InventoryProfiles::NONE;
    private ?string $sortBy        = null;
    private ?string $sortDir       = null;

    public function getBrand(): ?int
    {
        return $this->brand;
    }

    public function setBrand(?int $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getSupplier(): ?int
    {
        return $this->supplier;
    }

    public function setSupplier(?int $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getGeocode():?string
    {
        return $this->geocode;
    }

    public function setGeocode(?string $geocode): self
    {
        $this->geocode = $geocode;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(?bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function isQuoteOnly(): ?bool
    {
        return $this->quoteOnly;
    }

    public function setQuoteOnly(?bool $quoteOnly): self
    {
        $this->quoteOnly = $quoteOnly;

        return $this;
    }

    public function isEndOfLife(): ?bool
    {
        return $this->endOfLife;
    }

    public function setEndOfLife(?bool $endOfLife): self
    {
        $this->endOfLife = $endOfLife;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = (string)$state;

        return $this;
    }

    public function getReferenceCode(): ?string
    {
        return $this->referenceCode;
    }

    public function setReferenceCode(?string $code): self
    {
        $this->referenceCode = $code;

        return $this;
    }

    public function isBookmark(): ?bool
    {
        return $this->bookmark;
    }

    public function setBookmark(?bool $bookmark): self
    {
        $this->bookmark = $bookmark;

        return $this;
    }

    public function getProfile(): string
    {
        return $this->profile;
    }

    public function setProfile(string $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(?string $sortBy): self
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getSortDir(): ?string
    {
        return $this->sortDir;
    }

    public function setSortDir(?string $sortDir): self
    {
        $this->sortDir = $sortDir;

        return $this;
    }

    public function fromArray(array $array): void
    {
        if (10 !== count($array)) {
            return;
        }

        [
            $this->brand,
            $this->supplier,
            $this->reference,
            $this->designation,
            $this->geocode,
            $this->mode,
            $this->state,
            $this->profile,
            $this->sortBy,
            $this->sortDir,
        ] = $array;
    }

    public function toArray(): array
    {
        return [
            $this->brand,
            $this->supplier,
            $this->reference,
            $this->designation,
            $this->geocode,
            $this->mode,
            $this->state,
            $this->profile,
            $this->sortBy,
            $this->sortDir,
        ];
    }
}
