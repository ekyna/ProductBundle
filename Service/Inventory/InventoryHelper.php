<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Inventory;

use Ekyna\Bundle\ProductBundle\Repository\InventoryRepositoryInterface;

/**
 * Class InventoryHelper
 * @package Ekyna\Bundle\ProductBundle\Service\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryHelper
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository
    ) {
    }

    public function hasOpenedInventory(): bool
    {
        return null !== $this->repository->findOneOpened();
    }
}
