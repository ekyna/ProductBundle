<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Xls;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_filter;
use function array_push;
use function array_unique;
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
        $products = $this->productRepository->findForInventory();

        $file = new Xls('stock-products');

        $file->setHeaders([
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

            $data['geocode'] = implode(
                ',',
                array_unique(
                    array_filter(
                        $data['geocode'],
                        fn (?string $c) => !empty($c),
                    )
                )
            );

            $file->addRow($data);
        }

        return $file->download();
    }
}
