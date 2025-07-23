<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Xls;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        private readonly FormatterFactory             $formatterFactory,
        private readonly string                       $defaultCurrency
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $formatter = $this->formatterFactory->create();

        $stockUnits = $this->stockUnitRepository->findInStock();

        $file = new Xls('stock-units');

        $file->setHeaders([
            'id',
            'designation',
            'reference',
            'stock',
            'geocode',
            'buy price',
            'currency',
            'valorization',
            'exchange rate',
            'exchange date',
        ]);

        foreach ($stockUnits as $stockUnit) {
            $inStock = $stockUnit->getReceivedQuantity()
                + $stockUnit->getAdjustedQuantity()
                - $stockUnit->getShippedQuantity();

            /** @var ProductInterface $product */
            $product = $stockUnit->getSubject();
            $value = $price = $stockUnit->getNetPrice();

            $currency = ($c = $stockUnit->getCurrency()) ? $c->getCode() : $this->defaultCurrency;

            $exchangeRate = null;
            $exchangeDate = $stockUnit->getExchangeDate();
            if (null !== $exchangeRate = $stockUnit->getExchangeRate()) {
                $price = Money::round($price * $exchangeRate, $currency);
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
                $exchangeRate ? $exchangeRate->toFixed(5) : '',
                $exchangeDate ? $formatter->date($exchangeDate) : '',
            ];

            $file->addRow($data);
        }

        return $file->download();
    }
}
