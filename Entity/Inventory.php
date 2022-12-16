<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Model\InventoryState;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TimestampableTrait;

/**
 * Class Inventory
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Inventory extends AbstractResource implements InventoryInterface
{
    use TimestampableTrait;

    private InventoryState $state = InventoryState::NEW;

    public function __construct()
    {
        $this->initializeTimestampable();
    }

    public function __toString(): string
    {
        return $this->createdAt->format('Y-m-d');
    }

    public function getState(): InventoryState
    {
        return $this->state;
    }

    public function setState(InventoryState $state): InventoryInterface
    {
        $this->state = $state;

        return $this;
    }
}
