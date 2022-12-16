<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Inventory;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Model\InventoryState;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;

use function array_push;
use function array_unique;

/**
 * Class InventoryGenerator
 * @package Ekyna\Bundle\ProductBundle\Service\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryGenerator
{
    public function __construct(
        private readonly ProductRepositoryInterface   $productRepository,
        private readonly StockUnitRepositoryInterface $stockUnitRepository,
        private readonly EntityManagerInterface       $entityManager,
    ) {
    }

    public function generate(InventoryInterface $inventory): void
    {
        if ($inventory->getState() !== InventoryState::NEW) {
            throw new LogicException('Unexpected inventory state.');
        }

        $products = $this->productRepository->findForInventory();

        foreach ($products as $product) {
            $stockUnits = $this->stockUnitRepository->findLatestNotClosedBySubject($product);

            $geocodes = [];
            if (!empty($code = $product->getGeocode())) {
                $geocodes[] = $code;
            }
            foreach ($stockUnits as $stockUnit) {
                array_push($geocodes, ...$stockUnit->getGeocodes());
            }

            $inventoryProduct = new InventoryProduct();
            $inventoryProduct
                ->setInventory($inventory)
                ->setProduct($product)
                ->setGeocodes(array_unique($geocodes))
                ->setInitialStock(clone $product->getInStock());

            $this->entityManager->persist($inventoryProduct);
        }

        $inventory->setState(InventoryState::OPENED);
    }
}
