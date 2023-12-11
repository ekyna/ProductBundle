<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Inventory;

use Decimal\Decimal;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\Inventory;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Model\InventoryState;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\InventoryProductRepository;
use Ekyna\Component\Commerce\Stock\Helper\AdjustHelper;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentData;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;

use function gc_collect_cycles;
use function get_class;
use function sprintf;

/**
 * Class InventoryApplier
 * @package Ekyna\Bundle\ProductBundle\Service\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryApplier
{
    private Inventory $inventory;
    private string    $note;

    public function __construct(
        private readonly InventoryProductRepository $repository,
        private readonly InventoryCalculator        $calculator,
        private readonly AdjustHelper               $helper,
        private readonly EntityManagerInterface     $manager,
    ) {
    }

    public function apply(InventoryInterface $inventory): void
    {
        $this->inventory = $inventory;
        $this->note = $inventory->getCreatedAt()->format('Y-m-d') . ' inventory';

        $this->checkIfReady();

        $this->applyBundles();
        $this->applySimples();

        // $this->manager->merge($inventory); // Not working
        $inventory = $this->manager->find(get_class($inventory), $inventory->getId());

        $inventory->setState(InventoryState::CLOSED);
        $this->manager->persist($inventory);
        $this->manager->flush();
    }

    private function checkIfReady(): void
    {
        $bundles = $this->repository->findBundlesByInventory($this->inventory);

        foreach ($bundles as $bundle) {
            $this->checkBundle($bundle->getProduct());
        }
    }

    private function checkBundle(ProductInterface $bundle): void
    {
        foreach ($bundle->getBundleSlots() as $slot) {
            $choiceProduct = $slot->getChoices()->first()->getProduct();

            $inventoryProduct = $this
                ->repository
                ->findOneByInventoryAndProduct($this->inventory, $choiceProduct);

            if (null === $inventoryProduct) {
                throw new LogicException(
                    sprintf(
                        'Product [%s] %s (#{%s}) is missing from inventory.',
                        $choiceProduct,
                        $choiceProduct->getReference(),
                        $choiceProduct->getId(),
                    )
                );
            }

            if (null === $inventoryProduct->getRealStock()) {
                throw new LogicException(
                    sprintf(
                        'You must set real stock for product [%s] %s (#{%s}).',
                        $choiceProduct,
                        $choiceProduct->getReference(),
                        $choiceProduct->getId(),
                    )
                );
            }
        }
    }

    private function applyBundles(): void
    {
        while (null !== $product = $this->repository->findOneNotAppliedByInventory($this->inventory, true)) {
            $this->applyBundle($product);
        }
    }

    private function applySimples(): void
    {
        while (null !== $product = $this->repository->findOneNotAppliedByInventory($this->inventory, false)) {
            $this->applySimple($product);
        }
    }

    private function applyBundle(InventoryProduct $product): void
    {
        $quantity = $this->calculator->calculateBundleQuantityToApply($product);
        if ($quantity->isZero()) {
            return;
        }

        $this->applyBundleChildren($product->getProduct(), $quantity);

        $applied = $product->getAppliedStock() ?? new Decimal(0);
        $product->setAppliedStock($applied->add($quantity));
        $this->manager->persist($product);

        $this->manager->flush();
        $this->manager->clear();
        gc_collect_cycles();
    }

    private function applyBundleChildren(ProductInterface $bundle, Decimal $quantity): void
    {
        foreach ($bundle->getBundleSlots() as $slot) {
            $choice = $slot->getChoices()->first();
            $choiceProduct = $choice->getProduct();

            $inventoryProduct = $this->repository->findOneByInventoryAndProduct($this->inventory, $choiceProduct);
            if (!$inventoryProduct) {
                continue;
            }

            if (null === $realStock = $inventoryProduct->getRealStock()) {
                throw new LogicException('Real stock is not set.');
            }

            $childQuantity = $quantity->mul($choice->getMinQuantity());
            $inventoryProduct->setRealStock($realStock->add($childQuantity));

            $this->manager->persist($inventoryProduct);
        }
    }

    private function applySimple(InventoryProduct $product): void
    {
        $quantity = $this->calculator->calculateQuantityToApply($product);
        if ($quantity->isZero()) {
            return;
        }

        $data = new StockAdjustmentData($product->getProduct());
        $data->note = $this->note;
        $data->reason = $quantity->isNegative()
            ? StockAdjustmentReasons::REASON_DEBIT
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
