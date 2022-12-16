<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function fclose;
use function fopen;
use function fputcsv;
use function implode;

/**
 * Class ExportUnitsController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportUnitsController
{
    public function __construct(
        private readonly StockUnitRepositoryInterface $stockUnitRepository,
        private readonly string                       $defaultCurrency
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $response = new StreamedResponse();

        $response->setCallback(function () {
            if (false === $handle = fopen('php://output', 'w+')) {
                throw new RuntimeException('Failed to open output stream.');
            }

            $stockUnits = $this->stockUnitRepository->findInStock();

            fputcsv($handle, [
                'id',
                'designation',
                'reference',
                'stock',
                'geocode',
                'buy price',
                'currency',
                'valorization',
            ]);

            foreach ($stockUnits as $stockUnit) {
                $inStock = $stockUnit->getReceivedQuantity()
                    + $stockUnit->getAdjustedQuantity()
                    - $stockUnit->getShippedQuantity();

                /** @var ProductInterface $product */
                $product = $stockUnit->getSubject();
                $value = $price = $stockUnit->getNetPrice();

                $currency = ($c = $stockUnit->getCurrency()) ? $c->getCode() : $this->defaultCurrency;

                if ($rate = $stockUnit->getExchangeRate()) {
                    $price = Money::round($price * $rate, $currency);
                }

                $value = Money::round($value * $inStock, $currency);

                $data = [
                    $product->getId(),
                    (string)$product,
                    $product->getReference(),
                    $inStock,
                    implode(', ', $stockUnit->getGeocodes()),
                    Money::fixed($price, $currency),
                    $currency,
                    Money::fixed($value, $currency),
                ];

                fputcsv($handle, $data);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'inventory-units.csv'
        ));

        return $response;
    }
}
