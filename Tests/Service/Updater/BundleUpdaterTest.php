<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Service\Updater;

use Ekyna\Bundle\ProductBundle\Entity\BundleChoice;
use Ekyna\Bundle\ProductBundle\Entity\BundleSlot;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\BundleUpdater;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;
use PHPUnit\Framework\TestCase;

/**
 * Class BundleUpdaterTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Service\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleUpdaterTest extends TestCase
{
    /**
     * Tests the updateStock method.
     *
     * @param array $slots
     * @param array $result
     *
     * @dataProvider updateStockData
     */
    public function testUpdateStock($slots, $result)
    {
        $bundle = $this->createBundle($slots);

        /** @var PriceCalculator $calculator */
        $calculator = $this->createMock(PriceCalculator::class);
        $updater = new BundleUpdater($calculator);

        $updater->updateStock($bundle);

        $this->assertEquals($result['stockMode'], $bundle->getStockMode());
        $this->assertEquals($result['stockState'], $bundle->getStockState());
        $this->assertEquals($result['inStock'], $bundle->getInStock());
        $this->assertEquals($result['availableStock'], $bundle->getAvailableStock());
        $this->assertEquals($result['virtualStock'], $bundle->getVirtualStock());

        $expectedEda = $result['eda'] ? $result['eda']->format('Y-m-d') : null;
        $eda = $bundle->getEstimatedDateOfArrival() ? $bundle->getEstimatedDateOfArrival()->format('Y-m-d') : null;
        $this->assertEquals($expectedEda, $eda);
    }

    /**
     * Provides data for the testUpdateStock method.
     *
     * @return array
     */
    public function updateStockData()
    {
        return [
            'case_1' => [
                'slots'  => [
                    [
                        'minQuantity'    => 1,
                        'stockMode'      => StockSubjectModes::MODE_AUTO,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 1,
                        'availableStock' => 1,
                        'virtualStock'   => 1,
                        'eda'            => null,
                    ],
                ],
                'result' => [
                    'stockMode'      => StockSubjectModes::MODE_AUTO,
                    'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                    'inStock'        => 1,
                    'availableStock' => 1,
                    'virtualStock'   => 1,
                    'eda'            => null,
                ],
            ],
            'case_2' => [
                'slots'  => [
                    [   // Worst
                        'minQuantity'    => 4,
                        'stockMode'      => StockSubjectModes::MODE_AUTO,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 40,
                        'availableStock' => 20,
                        'virtualStock'   => 50,
                        'eda'            => new \DateTime('+2 day'),
                    ],
                    [
                        'minQuantity'    => 2,
                        'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 50,
                        'availableStock' => 50,
                        'virtualStock'   => 50,
                        'eda'            => null,
                    ],
                ],
                'result' => [
                    'stockMode'      => StockSubjectModes::MODE_AUTO,
                    'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                    'inStock'        => 10,
                    'availableStock' => 5,
                    'virtualStock'   => 12,
                    'eda'            => new \DateTime('+2 day'),
                ],
            ],
            'case_3' => [
                'slots'  => [
                    [
                        'minQuantity'    => 4,
                        'stockMode'      => StockSubjectModes::MODE_AUTO,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 100,
                        'availableStock' => 100,
                        'virtualStock'   => 100,
                        'eda'            => null,
                    ],
                    [
                        'minQuantity'    => 2,
                        'stockMode'      => StockSubjectModes::MODE_AUTO,
                        'stockState'     => StockSubjectStates::STATE_PRE_ORDER,
                        'inStock'        => 0,
                        'availableStock' => 0,
                        'virtualStock'   => 20,
                        'eda'            => new \DateTime('+2 day'),
                    ],
                    [   // Worst
                        'minQuantity'    => 1,
                        'stockMode'      => StockSubjectModes::MODE_AUTO,
                        'stockState'     => StockSubjectStates::STATE_PRE_ORDER,
                        'inStock'        => 0,
                        'availableStock' => 0,
                        'virtualStock'   => 8,
                        'eda'            => new \DateTime('+4 day'),
                    ],
                ],
                'result' => [
                    'stockMode'      => StockSubjectModes::MODE_AUTO,
                    'stockState'     => StockSubjectStates::STATE_PRE_ORDER,
                    'inStock'        => 0,
                    'availableStock' => 0,
                    'virtualStock'   => 8,
                    'eda'            => new \DateTime('+4 day'),
                ],
            ],
            'case_4' => [
                'slots'  => [
                    [
                        'minQuantity'    => 4,
                        'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 100,
                        'availableStock' => 100,
                        'virtualStock'   => 100,
                        'eda'            => null,
                    ],
                    [   // Worst eda
                        'minQuantity'    => 2,
                        'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 0,
                        'availableStock' => 0,
                        'virtualStock'   => 20,
                        'eda'            => null,
                    ],
                    [   // Worst virtual stock
                        'minQuantity'    => 1,
                        'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 0,
                        'availableStock' => 0,
                        'virtualStock'   => 8,
                        'eda'            => new \DateTime('+4 day'),
                    ],
                ],
                'result' => [
                    'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                    'inStock'        => 0,
                    'availableStock' => 0,
                    'virtualStock'   => 8,
                    'eda'            => new \DateTime('+4 day'),
                ],
            ],
            'case_5' => [
                'slots'  => [
                    [
                        'minQuantity'    => 4,
                        'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 100,
                        'availableStock' => 100,
                        'virtualStock'   => 100,
                        'eda'            => null,
                    ],
                    [
                        'minQuantity'    => 2,
                        'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 0,
                        'availableStock' => 0,
                        'virtualStock'   => 20,
                        'eda'            => new \DateTime('+2 day'),
                    ],
                    [   // Worst eda
                        'minQuantity'    => 2,
                        'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                        'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                        'inStock'        => 0,
                        'availableStock' => 0,
                        'virtualStock'   => 20,
                        'eda'            => new \DateTime('+4 day'),
                    ],
                ],
                'result' => [
                    'stockMode'      => StockSubjectModes::MODE_JUST_IN_TIME,
                    'stockState'     => StockSubjectStates::STATE_IN_STOCK,
                    'inStock'        => 0,
                    'availableStock' => 0,
                    'virtualStock'   => 10,
                    'eda'            => new \DateTime('+4 day'),
                ],
            ],
        ];
    }

    /**
     * Creates a bundle from the given slots data.
     *
     * @param array $data
     *
     * @return Product
     */
    private function createBundle(array $data)
    {
        $bundle = new Product();
        $bundle->setType(ProductTypes::TYPE_BUNDLE);

        foreach ($data as $datum) {
            $product = new Product();
            $product
                ->setStockMode($datum['stockMode'])
                ->setStockState($datum['stockState'])
                ->setInStock($datum['inStock'])
                ->setAvailableStock($datum['availableStock'])
                ->setVirtualStock($datum['virtualStock'])
                ->setEstimatedDateOfArrival($datum['eda']);

            $choice = new BundleChoice();
            $choice
                ->setProduct($product)
                ->setMinQuantity($datum['minQuantity']);

            $slot = new BundleSlot();
            $slot->addChoice($choice);

            $bundle->addBundleSlot($slot);
        }

        return $bundle;
    }
}