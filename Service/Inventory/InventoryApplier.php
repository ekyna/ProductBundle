<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Inventory;

use Decimal\Decimal;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Model\InventoryState;
use Ekyna\Bundle\ProductBundle\Repository\InventoryProductRepository;
use Ekyna\Component\Commerce\Stock\Helper\AdjustHelper;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentData;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;

use function gc_collect_cycles;
use function get_class;

/**
 * Class InventoryApplier
 * @package Ekyna\Bundle\ProductBundle\Service\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryApplier
{
    private string $note;

    public function __construct(
        private readonly InventoryProductRepository $repository,
        private readonly InventoryCalculator        $calculator,
        private readonly AdjustHelper               $helper,
        private readonly EntityManagerInterface     $manager,
    ) {
    }

    public function apply(InventoryInterface $inventory): void
    {
        $this->note = $inventory->getCreatedAt()->format('Y-m-d') . ' inventory';

        while (null !== $product = $this->repository->findOneNotAppliedByInventory($inventory)) {
            $this->applyProduct($product);
        }

        // $this->manager->merge($inventory); // Not working
        $inventory = $this->manager->find(get_class($inventory), $inventory->getId());

        $inventory->setState(InventoryState::CLOSED);
        $this->manager->persist($inventory);
        $this->manager->flush();
    }

    private function applyProduct(InventoryProduct $product): void
    {
        $quantity = $this->calculator->calculateQuantityToApply($product);

        if ($quantity->isZero()) {
            return;
        }

        $data = new StockAdjustmentData($product->getProduct());
        $data->note = $this->note;
        $data->reason = $quantity->isNegative() ? StockAdjustmentReasons::REASON_DEBIT
            : StockAdjustmentReasons::REASON_CREDIT;
        $data->quantity = $quantity->abs();

        $this->helper->adjust($data);

        $applied = $product->getAppliedStock() ?? new Decimal(0);

        $product->setAppliedStock($applied->add($quantity));
        $this->manager->persist($product);

        $this->manager->flush();
        $this->manager->clear();
        gc_collect_cycles();
    }
}
