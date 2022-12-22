<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;

/**
 * Class CatalogPage
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogPage
{
    private ?int              $id       = null;
    private ?CatalogInterface $catalog  = null;
    private ?int              $number   = null;
    private ?string           $template = null;
    /** @var Collection<int, CatalogSlot> */
    private Collection $slots;
    private array      $options;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCatalog(): ?CatalogInterface
    {
        return $this->catalog;
    }

    public function setCatalog(?CatalogInterface $catalog): CatalogPage
    {
        if ($catalog === $this->catalog) {
            return $this;
        }

        if ($previous = $this->catalog) {
            $this->catalog = null;
            $previous->removePage($this);
        }

        if ($this->catalog = $catalog) {
            $this->catalog->addPage($this);
        }

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): CatalogPage
    {
        $this->number = $number;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): CatalogPage
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return Collection<int, CatalogSlot>
     */
    public function getSlots(): Collection
    {
        return $this->slots;
    }

    public function addSlot(CatalogSlot $slot): CatalogPage
    {
        if (!$this->slots->contains($slot)) {
            $this->slots->add($slot);
            $slot->setPage($this);
        }

        return $this;
    }

    public function removeSlot(CatalogSlot $slot): CatalogPage
    {
        if ($this->slots->contains($slot)) {
            $this->slots->removeElement($slot);
            $slot->setPage(null);
        }

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(array $options = null): CatalogPage
    {
        $this->options = $options;

        return $this;
    }
}
