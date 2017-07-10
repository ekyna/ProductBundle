<?php

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Class InventorySearch
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventorySearch
{
    /**
     * @var int
     */
    private $brand;

    /**
     * @var int
     */
    private $supplier;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $designation;

    /**
     * @var string
     */
    private $geocode;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $sortBy;

    /**
     * @var string
     */
    private $sortDir;


    /**
     * Returns the brand.
     *
     * @return int
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Sets the brand.
     *
     * @param int $brand
     *
     * @return InventorySearch
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Returns the supplier.
     *
     * @return int
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Sets the supplier.
     *
     * @param int $supplier
     *
     * @return InventorySearch
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return InventorySearch
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return InventorySearch
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Returns the geocode.
     *
     * @return string
     */
    public function getGeocode()
    {
        return $this->geocode;
    }

    /**
     * Sets the geocode.
     *
     * @param string $geocode
     *
     * @return InventorySearch
     */
    public function setGeocode($geocode)
    {
        $this->geocode = $geocode;

        return $this;
    }

    /**
     * Returns the mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Sets the mode.
     *
     * @param string $mode
     *
     * @return InventorySearch
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the state.
     *
     * @param string $state
     *
     * @return InventorySearch
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Returns the sort by.
     *
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Sets the sort by.
     *
     * @param string $sortBy
     *
     * @return InventorySearch
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * Returns the sort dir.
     *
     * @return string
     */
    public function getSortDir()
    {
        return $this->sortDir;
    }

    /**
     * Sets the sort dir.
     *
     * @param string $sortDir
     *
     * @return InventorySearch
     */
    public function setSortDir($sortDir)
    {
        $this->sortDir = $sortDir;

        return $this;
    }

    /**
     * Loads form the given array.
     *
     * @param array $array
     */
    public function fromArray(array $array)
    {
        if (9 === count($array)) {
            list(
                $this->brand,
                $this->supplier,
                $this->reference,
                $this->designation,
                $this->geocode,
                $this->mode,
                $this->state,
                $this->sortBy,
                $this->sortDir
            ) = $array;
        }
    }

    /**
     * Converts to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            $this->brand,
            $this->supplier,
            $this->reference,
            $this->designation,
            $this->geocode,
            $this->mode,
            $this->state,
            $this->sortBy,
            $this->sortDir
        ];
    }
}
