<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory;

use Ekyna\Bundle\ProductBundle\Service\Stock\Inventory;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Trait InventoryTrait
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait InventoryTrait
{
    protected Inventory $inventory;

    public function setInventory(Inventory $inventory): void
    {
        $this->inventory = $inventory;
    }

    protected function respond(array $ids): JsonResponse
    {
        $products = $this->inventory->findProducts($ids);

        $data = [
            'products' => $products,
            'update'   => true,
        ];

        return new JsonResponse($data);
    }
}
