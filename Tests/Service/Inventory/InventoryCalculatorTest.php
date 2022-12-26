<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Tests\Service\Inventory;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Class InventoryCalculatorTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Service\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryCalculatorTest extends TestCase
{
    /**
     * @dataProvider provideCalculateQuantityToApply
     */
    public function testCalculateQuantityToApply(int $initial, int $real, int $applied, int $expected): void
    {
        $product = new InventoryProduct();
        $product
            ->setInitialStock(new Decimal($initial))
            ->setRealStock(new Decimal($real))
            ->setAppliedStock(new Decimal($applied));

        $calculator = new InventoryCalculator();

        $actual = $calculator->calculateQuantityToApply($product);

        self::assertEquals(new Decimal($expected), $actual);
    }

    public function provideCalculateQuantityToApply(): array
    {
        return [
            // Initial, Real, Applied, Expected
            [30, 35, -5, 10],
            [30, 35, 0, 5],
            [30, 35, 5, 0],
            [30, 35, 10, -5],
            [30, 25, 5, -10],
            [30, 25, 0, -5],
            [30, 25, -5, 0],
            [30, 25, -10, 5],
        ];
    }
}
