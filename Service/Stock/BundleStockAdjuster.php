<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Bundle\ProductBundle\Model\BundleStockAdjustment;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductStockUnitInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductStockUnitRepository;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

use function array_push;

use const INF;

/**
 * Class BundleStockAdjuster
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleStockAdjuster
{
    private BundleStockAdjustment $model;

    public function __construct(
        private readonly ResourceFactoryInterface   $stockAdjustmentFactory,
        private readonly ProductStockUnitRepository $stockUnitRepository,
        private readonly ResourceFactoryInterface   $stockUnitFactory,
        private readonly ManagerFactoryInterface $managerFactory,
    ) {
    }

    public function apply(BundleStockAdjustment $adjustment): void
    {
        if (!ProductTypes::isBundleType($adjustment->bundle)) {
            throw new UnexpectedValueException('Expected bundle product');
        }

        $this->model = $adjustment;

        $this->applyProduct($adjustment->bundle, $adjustment->quantity);
    }

    private function applyProduct(ProductInterface $product, Decimal $quantity): void
    {
        if (ProductTypes::isBundleType($product)) {
            foreach ($product->getBundleSlots() as $slot) {
                $childChoice = $slot->getChoices()->first();
                $childProduct = $childChoice->getProduct();
                $childQuantity = $quantity->mul($childChoice->getMinQuantity());

                $this->applyProduct($childProduct, $childQuantity);
            }

            return;
        }

        if (StockSubjectModes::MODE_DISABLED === $product->getStockMode()) {
            return;
        }

        if (StockAdjustmentReasons::isDebitReason($this->model->reason)) {
            $this->debitProduct($product, $quantity);

            return;
        }

        $this->creditProduct($product, $quantity);
    }

    private function debitProduct(ProductInterface $product, Decimal $quantity): void
    {
        $units = $this->getChildStockUnits($product, true);

        foreach ($units as $unit) {
            $qty = min($unit->getShippableQuantity(), $quantity);

            $adjustment = $this->createAdjustment();
            $adjustment
                ->setStockUnit($unit)
                ->setQuantity($qty);

            $this->getAdjustmentManager()->persist($adjustment);

            $quantity -= $qty;

            if ($quantity->isZero()) {
                return;
            }
        }

        if (!$quantity->isZero()) {
            throw new StockLogicException('Failed to adjust bundle child.');
        }
    }

    private function creditProduct(ProductInterface $product, Decimal $quantity): void
    {
        $units = $this->getChildStockUnits($product, false);

        if (null === $unit = array_shift($units)) {
            /** @var StockUnitInterface $unit */
            $unit = $this->stockUnitFactory->create();
            $unit->setSubject($product);

            $this->getUnitManager()->persist($unit); // Should be stockUnitManager.
        }

        $adjustment = $this->createAdjustment();
        $adjustment
            ->setStockUnit($unit)
            ->setQuantity($quantity);

        $this->getAdjustmentManager()->persist($adjustment);
    }

    private function createAdjustment(): StockAdjustmentInterface
    {
        /** @var StockAdjustmentInterface $adjustment */
        $adjustment = $this->stockAdjustmentFactory->create();
        $adjustment
            ->setReason($this->model->reason)
            ->setNote($this->model->note);

        return $adjustment;
    }

    public function calculateMaxDebit(BundleStockAdjustment $adjustment): Decimal
    {
        if (!ProductTypes::isBundleType($adjustment->bundle)) {
            throw new UnexpectedValueException('Expected bundle product');
        }

        return $this->calculateProductMaxDebit($adjustment->bundle);
    }

    private function calculateProductMaxDebit(ProductInterface $product): Decimal
    {
        if (ProductTypes::isBundleType($product)) {
            $quantity = new Decimal(INF);
            foreach ($product->getBundleSlots() as $slot) {
                $childChoice = $slot->getChoices()->first();
                $childProduct = $childChoice->getProduct();

                $childQuantity = $this
                    ->calculateProductMaxDebit($childProduct)
                    ->div($childChoice->getMinQuantity());

                if ($quantity > $childQuantity) {
                    $quantity = $childQuantity;
                }
            }

            return $quantity;
        }

        if (ProductTypes::isVariableType($product) || ProductTypes::isConfigurableType($product)) {
            throw new UnexpectedValueException('Unexpected product type.');
        }

        if (StockSubjectModes::MODE_DISABLED === $product->getStockMode()) {
            return new Decimal(INF);
        }

        $quantity = new Decimal(0);

        $units = $this->getChildStockUnits($product, true);

        foreach ($units as $unit) {
            $quantity += $unit->getShippableQuantity();
        }

        return $quantity;
    }

    /**
     * @return iterable<int, StockUnitInterface>
     */
    private function getChildStockUnits(ProductInterface $product, bool $debitIntent): iterable
    {
        $units = $this->stockUnitRepository->findNotClosedBySubject($product);

        if (!$debitIntent) {
            array_push($units, ...$this->stockUnitRepository->findLatestClosedBySubject($product, 1));
        }

        // TODO Sort ?

        return $units;
    }

    private function getAdjustmentManager(): ResourceManagerInterface
    {
        return $this->managerFactory->getManager(StockAdjustmentInterface::class);
    }

    private function getUnitManager(): ResourceManagerInterface
    {
        return $this->managerFactory->getManager(ProductStockUnitInterface::class);
    }
}
