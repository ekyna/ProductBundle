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

use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;

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
    private array     $errors;

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

        $this->errors = [];
        $this->checkIfReady();

        if (!empty($this->errors)) {
            throw new LogicException();
        }

        $this->applyBundles();
        $this->applySimples();

        // $this->manager->merge($inventory); // Not working
        $inventory = $this->manager->find(get_class($inventory), $inventory->getId());

        $inventory->setState(InventoryState::CLOSED);
        $this->manager->persist($inventory);
        $this->manager->flush();
    }

    public function getErrors(): array
    {
        return $this->errors;
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

            if (StockSubjectModes::MODE_DISABLED === $choiceProduct->getStockMode()) {
                continue;
            }

            $inventoryProduct = $this
                ->repository
                ->findOneByInventoryAndProduct($this->inventory, $choiceProduct);

            if (null === $inventoryProduct) {
                $this->errors[] = sprintf(
                    '[%s] %s (#{%s}) is missing. Needed for bundle [%s] %s (#{%s}).',
                    $choiceProduct->getReference(),
                    $choiceProduct,
                    $choiceProduct->getId(),
                    $bundle->getReference(),
                    $bundle,
                    $bundle->getId(),
                );

                continue;
            }

            if (null === $inventoryProduct->getRealStock()) {
                $this->errors[] = sprintf(
                    '[%s] %s (#{%s}) is missing. Needed for bundle [%s] %s (#{%s}).',
                    $choiceProduct->getReference(),
                    $choiceProduct,
                    $choiceProduct->getId(),
                    $bundle->getReference(),
                    $bundle,
                    $bundle->getId(),
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
