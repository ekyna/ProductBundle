<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface InventoryRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface InventoryRepositoryInterface extends ResourceRepositoryInterface
{
    public function findOneOpened(): ?InventoryInterface;

    public function findOneNotClosed(): ?InventoryInterface;
}
