<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Service\Stock\StockView;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Trait StockViewTrait
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait StockViewTrait
{
    protected StockView $inventory;

    public function setStockView(StockView $inventory): void
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
