<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Service\Stock\StockView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ListController
{
    public function __construct(
        private readonly StockView $inventory
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $products = $this->inventory->listProducts($request, false, ['method' => 'GET']);

        $data = [
            'products' => $products,
        ];

        return new JsonResponse($data);
    }
}
