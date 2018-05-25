<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @var ArrayCollection|CatalogSlot[]
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the catalog.
     *
     * @return Catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Sets the catalog.
     *
     * @param Catalog $catalog
     *
     * @return CatalogPage
     */
    public function setCatalog(Catalog $catalog = null)
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
    public function getNumber()
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
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Returns the template.
     *
     * @return string
     */
    public function getTemplate()
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
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Returns the slots.
     *
     * @return ArrayCollection|CatalogSlot[]
     */
    public function getSlots()
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
    public function addSlot(CatalogSlot $slot)
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
    public function removeSlot(CatalogSlot $slot)
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options.
     *
     * @param array $options
     *
     * @return CatalogPage
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }
}