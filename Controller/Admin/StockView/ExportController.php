<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Service\Stock\StockView;
use Ekyna\Component\Resource\Helper\File\Xls;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_fill_keys;
use function array_keys;
use function array_values;

/**
 * Class ExportController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportController
{
    public function __construct(
        private readonly StockView $inventory
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $products = $this->inventory->listProducts($request, true);

        $file = new Xls('inventory-products');

        $keys = [
            'id'              => 'ID',
            'type'            => 'Type',
            'brand'           => 'Brand',
            'reference'       => 'Reference',
            'designation'     => 'Designation',
            'net_price'       => 'Net price',
            'weight'          => 'Weight',
            'geocode'         => 'Geocode',
            'visible'         => 'Visible',
            'quote_only'      => 'Quote only',
            'end_of_life'     => 'End of life',
            'stock_mode'      => 'Stock mode',
            'stock_state'     => 'Stock state',
            'stock_floor'     => 'Stock floor',
            'replenishment'   => 'Replenishment',
            'in_stock'        => 'In stock',
            'available_stock' => 'Available stock',
            'virtual_stock'   => 'Virtual stock',
            'eda'             => 'Estimated date of arrival',
            'pending'         => 'Pending quantity',
            'ordered'         => 'Ordered quantity',
            'received'        => 'Received quantity',
            'adjusted'        => 'Adjusted quantity',
            'sold'            => 'Sold quantity',
            'shipped'         => 'Shipped quantity',
        ];

        $file->setHeaders(array_values($keys));

        $defaults = array_fill_keys(array_keys($keys), null);

        $products = array_map(function ($product) use ($defaults) {
            if (empty($product['designation'])) {
                $product['designation'] = trim(
                    $product['parent_designation'] . ' ' . $product['attributes_designation']
                );
            }
            unset($product['parent_designation']);
            unset($product['attributes_designation']);

            return array_intersect_key(array_replace($defaults, $product), $defaults);
        }, $products);

        $file->addRows($products);

        return $file->download([
            'inline' => true,
        ]);
    }
}
