<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\TimestampableInterface;

/**
 * Interface InventoryInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface InventoryInterface extends TimestampableInterface, ResourceInterface
{
    public function getState(): InventoryState;

    public function setState(InventoryState $state): InventoryInterface;
}
