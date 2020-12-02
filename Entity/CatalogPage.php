<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class CatalogPage
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogPage
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Catalog
     */
    private $catalog;

    /**
     * @var int
     */
    private $number;

    /**
     * @var string
     */
    private $template;

    /**
     * @var Collection|CatalogSlot[]
     */
    private $slots;

    /**
     * @var array
     */
    private $options;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the catalog.
     *
     * @return Catalog|null
     */
    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    /**
     * Sets the catalog.
     *
     * @param Catalog|null $catalog
     *
     * @return CatalogPage
     */
    public function setCatalog(Catalog $catalog = null): CatalogPage
    {
        if ($catalog !== $this->catalog) {
            if ($previous = $this->catalog) {
                $this->catalog = null;
                $previous->removePage($this);
            }

            if ($this->catalog = $catalog) {
                $this->catalog->addPage($this);
            }
        }

        return $this;
    }

    /**
     * Returns the number.
     *
     * @return int
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * Sets the number.
     *
     * @param int $number
     *
     * @return CatalogPage
     */
    public function setNumber(int $number): CatalogPage
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Returns the template.
     *
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Sets the template.
     *
     * @param string $template
     *
     * @return CatalogPage
     */
    public function setTemplate(string $template): CatalogPage
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Returns the slots.
     *
     * @return Collection|CatalogSlot[]
     */
    public function getSlots(): Collection
    {
        return $this->slots;
    }

    /**
     * Adds the slots.
     *
     * @param CatalogSlot $slot
     *
     * @return CatalogPage
     */
    public function addSlot(CatalogSlot $slot): CatalogPage
    {
        if (!$this->slots->contains($slot)) {
            $this->slots->add($slot);
            $slot->setPage($this);
        }

        return $this;
    }

    /**
     * Removes the slots.
     *
     * @param CatalogSlot $slot
     *
     * @return CatalogPage
     */
    public function removeSlot(CatalogSlot $slot): CatalogPage
    {
        if ($this->slots->contains($slot)) {
            $this->slots->removeElement($slot);
            $slot->setPage(null);
        }

        return $this;
    }

    /**
     * Returns the options.
     *
     * @return array|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Sets the options.
     *
     * @param array|null $options
     *
     * @return CatalogPage
     */
    public function setOptions(array $options = null): CatalogPage
    {
        $this->options = $options;

        return $this;
    }
}
