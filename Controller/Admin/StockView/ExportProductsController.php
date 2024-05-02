<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function array_push;
use function array_unique;
use function fclose;
use function fopen;
use function fputcsv;
use function implode;

/**
 * Class ExportProductsController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportProductsController
{
    public function __construct(
        private readonly ProductRepositoryInterface   $productRepository,
        private readonly StockUnitRepositoryInterface $stockUnitRepository
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $response = new StreamedResponse();

        $response->setCallback(function () {
            if (false === $handle = fopen('php://output', 'w+')) {
                throw new RuntimeException('Failed to open output stream.');
            }

            $products = $this->productRepository->findForInventory();

            fputcsv($handle, [
                'id',
                'type',
                'designation',
                'reference',
                'stock',
                'geocode',
            ]);

            foreach ($products as $product) {
                if (ProductTypes::TYPE_CONFIGURABLE === $product->getType()) {
                    continue;
                }

                $data = [
                    'id'          => $product->getId(),
                    'type'        => $product->getType(),
                    'designation' => $product->getFullDesignation(true),
                    'reference'   => $product->getReference(),
                    'in_stock'    => $product->getInStock()->toFixed(3),
                    'geocode'     => [$product->getGeocode()],
                ];

                if (ProductTypes::isChildType($product)) {
                    $units = $this->stockUnitRepository->findReadyBySubject($product);

                    foreach ($units as $unit) {
                        array_push($data['geocode'], ...$unit->getGeocodes());
                    }
                }

                $data['geocode'] = implode(',', array_unique($data['geocode']));

                fputcsv($handle, $data);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'inventory-products.csv'
            )
        );

        return $response;
    }
}
