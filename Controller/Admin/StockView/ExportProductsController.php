<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function fclose;
use function fopen;
use function fputcsv;

/**
 * Class ExportProductsController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportProductsController
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
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
                'designation',
                'reference',
                'stock',
                'geocode',
            ], ';', '"');

            foreach ($products as $product) {
                $data = [
                    $product->getId(),
                    $product->getFullDesignation(true),
                    $product->getReference(),
                    $product->getInStock()->toFixed(3),
                    $product->getGeocode(),
                ];

                fputcsv($handle, $data);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'inventory-products.csv'
        ));

        return $response;
    }
}
