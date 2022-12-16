<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class InventoryProduct
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryProduct
{
    private ?int               $id           = null;
    private ?Inventory         $inventory    = null;
    private ?ProductInterface  $product      = null;
    private array              $geocodes     = [];
    private Decimal            $initialStock;
    private ?Decimal           $realStock    = null;
    private ?Decimal           $appliedStock = null;
    private ?DateTimeInterface $updatedAt    = null;

    public function __construct()
    {
        $this->initialStock = new Decimal(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): InventoryProduct
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): InventoryProduct
    {
        $this->product = $product;

        return $this;
    }

    public function getGeocodes(): array
    {
        return $this->geocodes ?? [];
    }

    public function setGeocodes(array $geocodes): InventoryProduct
    {
        $this->geocodes = $geocodes;

        return $this;
    }

    public function getInitialStock(): Decimal
    {
        return $this->initialStock;
    }

    public function setInitialStock(Decimal $initialStock): InventoryProduct
    {
        $this->initialStock = $initialStock;

        return $this;
    }

    public function getRealStock(): ?Decimal
    {
        return $this->realStock;
    }

    public function setRealStock(?Decimal $realStock): InventoryProduct
    {
        $this->realStock = $realStock;

        return $this;
    }

    public function getAppliedStock(): ?Decimal
    {
        return $this->appliedStock;
    }

    public function setAppliedStock(?Decimal $appliedStock): InventoryProduct
    {
        $this->appliedStock = $appliedStock;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): InventoryProduct
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
