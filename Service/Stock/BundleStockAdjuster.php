<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stock;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Stock\Helper\AdjustHelper;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentData;

use const INF;

/**
 * Class BundleStockAdjuster
 * @package Ekyna\Bundle\ProductBundle\Service\Stock
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleStockAdjuster
{
    private StockAdjustmentData $data;

    public function __construct(
        private readonly AdjustHelper $helper,
    ) {
    }

    public function apply(StockAdjustmentData $data): void
    {
        $subject = $this->getProduct($data);

        $this->data = $data;

        $this->applyProduct($subject, $data->quantity);
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

        $this->helper->adjust(new StockAdjustmentData(
            $product,
            $quantity,
            $this->data->reason,
            $this->data->note
        ));
    }

    public function calculateMaxDebit(StockAdjustmentData $data): Decimal
    {
        $subject = $this->getProduct($data);

        $this->data = $data;

        return $this->calculateProductMaxDebit($subject);
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

        return $this->helper->calculateMaxDebit($product);
    }

    private function getProduct(StockAdjustmentData $data): ProductInterface
    {
        $subject = $data->subject;

        if (!$subject instanceof ProductInterface) {
            throw new UnexpectedTypeException($subject, ProductInterface::class);
        }

        if (!ProductTypes::isBundleType($subject)) {
            throw new UnexpectedValueException('Expected bundle product');
        }

        return $subject;
    }
}
