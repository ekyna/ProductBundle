<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class OfferResolverTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferResolverTest extends TestCase
{
    /**
     * @var OfferResolver
     */
    private $resolver;

    /**
     * @var Repository\PricingRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pricingRepository;

    /**
     * @var Repository\SpecialOfferRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $specialOfferRepository;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->pricingRepository = $this->createMock(Repository\PricingRepository::class);
        $this->specialOfferRepository = $this->createMock(Repository\SpecialOfferRepository::class);
        $this->resolver = new OfferResolver($this->pricingRepository, $this->specialOfferRepository);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        $this->pricingRepository = null;
        $this->specialOfferRepository = null;
        $this->resolver = null;
    }


    /**
     * @param array $pricing
     * @param array $specialOffer
     * @param array $expected
     *
     * @dataProvider resolveDataProvider
     */
    public function testResolve($pricing, $specialOffer, $expected)
    {
        $brand = $this->createMock(Model\BrandInterface::class);
        $product = $this->createMock(Model\ProductInterface::class);
        $product
            ->method('getBrand')
            ->willReturn($brand);

        $this->pricingRepository
            ->method('findRulesByBrand')
            ->with($brand)
            ->willReturn($pricing);

        $this->specialOfferRepository
            ->method('findRulesByProduct')
            ->with($product)
            ->willReturn($specialOffer);

        $result = $this->resolver->resolve($product);

        $this->assertEquals($expected, $result);
    }

    /**
     * Provides test data for testResolve().
     *
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            // Special offer has higher percentage than pricing rule
            // -> merge special offer (percentage)
            'test case 1'  => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
            ],
            // Special offer has lower percentage than pricing rule
            // -> keep pricing rule, ignore special offer
            'test case 2'  => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
            ],
            // Special offer applies to pricing rule case (by group) but has lower percentage
            // -> keep pricing rule, add special offer
            'test case 3'  => [
                'pricing'       => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'offers'        => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
            ],
            // Special offer applies to pricing rule case (by group) and has higher percentage
            // -> merge special offer (group and percentage)
            'test case 4'  => [
                'pricing'       => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
            ],
            // Pricing rule applies to special offer case (by group) and has higher percentage
            // -> keep pricing rule, ignore special offer
            'test case 5'  => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
            ],
            // Pricing rule applies to special offer case (by group) but has lower percentage
            // -> keep pricing rule, add special offer
            'test case 6'  => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
            ],
            // Special offer applies to pricing rule case (by country) but has lower percentage
            // -> keep pricing rule, add special offer
            'test case 7'  => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
            ],
            // Special offer applies to pricing rule case (by country) and has higher percentage
            // -> merge special offer (country and percent)
            'test case 8'  => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
            ],
            // Pricing rule applies to special offer case (by country) and has higher percentage
            // -> keep pricing rule, ignore special offer
            'test case 9'  => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 10],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
            ],
            // Pricing rule applies to special offer case (by country) but has lower percentage
            // -> keep pricing rule, add special offer
            'test case 10' => [
                'pricing'       => [
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
                    ['group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
            ],
            // Distinct case, special offer has higher percentage
            // -> keep pricing rule, add special offer
            'test case 11' => [
                'pricing'       => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                    ['group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
                ],
            ],

            // Common case, special offer replace the 2 last discount rules
            'test case 12' => [
                'pricing'       => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 10],
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 12],
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 15],
                ],
                'special_offer' => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 15, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 15, 'percent' => 20],
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 10],
                ],
            ],

            // Special offer replaces discount rules for group 1
            'test case 13' => [
                'pricing'       => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 10],
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 12],
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 15],
                    ['group_id' => 2, 'country_id' => null, 'min_qty' => 10, 'percent' => 15],
                    ['group_id' => 2, 'country_id' => null, 'min_qty' => 20, 'percent' => 17],
                    ['group_id' => 2, 'country_id' => null, 'min_qty' => 30, 'percent' => 20],
                ],
                'special_offer' => [
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'offers'        => [
                    ['group_id' => 2, 'country_id' => null, 'min_qty' => 30, 'percent' => 20],
                    ['group_id' => 2, 'country_id' => null, 'min_qty' => 20, 'percent' => 17],
                    ['group_id' => 2, 'country_id' => null, 'min_qty' => 10, 'percent' => 15],
                    ['group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
            ],


        ];
    }
}
