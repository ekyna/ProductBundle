<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory;

use Ekyna\Bundle\ProductBundle\Service\Stock\Inventory;
use Ekyna\Component\Resource\Helper\File\Csv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExportController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportController
{
    private Inventory $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public function __invoke(Request $request): Response
    {
        $products = $this->inventory->listProducts($request, true);

        $csv = Csv::create('inventory-products.csv');

        $csv->addRow([
            'id',
            'type',
            'brand',
            'net_price',
            'weight',
            'reference',
            'designation',
            'attributes_designation',
            'geocode',
            'visible',
            'quote_only',
            'end_of_life',
            'stock_mode',
            'stock_state',
            'stock_floor',
            'replenishment',
            'in_stock',
            'available_stock',
            'virtual_stock',
            'estimated_date_of_arrival',
            'parent_designation',
            'pending_quantity',
            'ordered_quantity',
            'received_quantity',
            'adjusted_quantity',
            'sold_quantity',
            'shipped_quantity',
        ]);

        $csv->addRows($products);

        return $csv->download([
            'inline' => true,
        ]);
    }
}
