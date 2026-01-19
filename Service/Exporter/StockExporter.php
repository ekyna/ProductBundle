<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Exporter;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Xls;

use function array_push;
use function array_unique;
use function implode;

/**
 * Class StockExporter
 * @package Ekyna\Bundle\ProductBundle\Service\Exporter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockExporter
{
    public function __construct(
        private readonly StockUnitRepositoryInterface $stockUnitRepository,
        private readonly string                       $defaultCurrency
    ) {
    }

    public function export(): Xls
    {
        $file = new Xls('stock-valuation');

        $file->setHeaders([
            'id',
            'designation',
            'reference',
            'EOL',
            'stock',
            'value',
            'geocodes',
        ]);

        $products = $this->loadProducts();

        foreach ($products as $product) {
            $file->addRow([
                $product['id'],
                $product['designation'],
                $product['reference'],
                $product['EOL'],
                $product['stock'],
                Money::fixed($product['value'], $this->defaultCurrency),
                implode(', ', $product['geocodes']),
            ]);
        }

        return $file;
    }

    private function loadProducts(): array
    {
        $stockUnits = $this->stockUnitRepository->findInStock();

        $products = [];

        foreach ($stockUnits as $stockUnit) {
            $inStock = $stockUnit->getReceivedQuantity()
                + $stockUnit->getAdjustedQuantity()
                - $stockUnit->getShippedQuantity();

            /** @var ProductInterface $product */
            $product = $stockUnit->getSubject();
            $price = $stockUnit->getNetPrice();

            if (!isset($products[$id = $product->getId()])) {
                $products[$id] = [
                    'id'          => $id,
                    'designation' => (string)$product,
                    'reference'   => $product->getReference(),
                    'EOL'         => $product->isEndOfLife() ? 'Yes' : 'No',
                    'stock'       => new Decimal(0),
                    'value'       => new Decimal(0),
                    'geocodes'    => [],
                ];
            }

            $products[$id]['stock'] = $products[$id]['stock']->add($inStock);

            $products[$id]['value'] = $products[$id]['value']->add($price->mul($inStock));

            array_push($products[$id]['geocodes'], ...$stockUnit->getGeocodes());
            $products[$id]['geocodes'] = array_unique($products[$id]['geocodes']);
        }

        return $products;
    }
}
