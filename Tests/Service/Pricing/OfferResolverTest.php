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
        /** @var Model\ProductInterface|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->createMock(Model\ProductInterface::class);
        $product
            ->method('getNetPrice')
            ->willReturn(100);

        $this->pricingRepository
            ->method('findRulesByProduct')
            ->with($product)
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
            // -> keep special offer
            'test case 1'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'special_offer_id' => 1,
                        'group_id'         => null,
                        'country_id'       => null,
                        'min_qty'          => 1,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                ],
            ],
            // Special offer has lower percentage than pricing rule
            // -> keep pricing rule
            'test case 2'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 1,
                        'group_id'   => null,
                        'country_id' => null,
                        'min_qty'    => 1,
                        'percent'    => 20,
                        'net_price'  => 80,
                        'details'    => [
                            'pricing' => 20,
                        ],
                    ],
                ],
            ],
            // Special offer applies to pricing rule case (by group) but has lower percentage
            // -> keep both
            'test case 3'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 1,
                        'group_id'   => 1,
                        'country_id' => null,
                        'min_qty'    => 1,
                        'percent'    => 20,
                        'net_price'  => 80,
                        'details'    => [
                            'pricing' => 20,
                        ],
                    ],
                    [
                        'special_offer_id' => 1,
                        'group_id'         => null,
                        'country_id'       => null,
                        'min_qty'          => 1,
                        'percent'          => 10,
                        'net_price'        => 90,
                        'details'          => [
                            'special' => 10,
                        ],
                    ],
                ],
            ],
            // Special offer applies to pricing rule case (by group) and has higher percentage
            // -> keep special offer
            'test case 4'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'special_offer_id' => 1,
                        'group_id'         => null,
                        'country_id'       => null,
                        'min_qty'          => 1,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                ],
            ],
            // Pricing rule applies to special offer case (by group) and has higher percentage
            // -> keep pricing rule
            'test case 5'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 1,
                        'group_id'   => null,
                        'country_id' => null,
                        'min_qty'    => 1,
                        'percent'    => 20,
                        'net_price'  => 80,
                        'details'    => [
                            'pricing' => 20,
                        ],
                    ],
                ],
            ],
            // Pricing rule applies to special offer case (by group) but has lower percentage
            // -> keep both
            'test case 6'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'special_offer_id' => 1,
                        'group_id'         => 1,
                        'country_id'       => null,
                        'min_qty'          => 1,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                    [
                        'pricing_id' => 1,
                        'group_id'   => null,
                        'country_id' => null,
                        'min_qty'    => 1,
                        'percent'    => 10,
                        'net_price'  => 90,
                        'details'    => [
                            'pricing' => 10,
                        ],
                    ],
                ],
            ],
            // Special offer applies to pricing rule case (by country) but has lower percentage
            // -> keep both
            'test case 7'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 1,
                        'group_id'   => null,
                        'country_id' => 1,
                        'min_qty'    => 1,
                        'percent'    => 20,
                        'net_price'  => 80,
                        'details'    => [
                            'pricing' => 20,
                        ],
                    ],
                    [
                        'special_offer_id' => 1,
                        'group_id'         => null,
                        'country_id'       => null,
                        'min_qty'          => 1,
                        'percent'          => 10,
                        'net_price'        => 90,
                        'details'          => [
                            'special' => 10,
                        ],
                    ],
                ],
            ],
            // Special offer applies to pricing rule case (by country) and has higher percentage
            // -> keep special offer
            'test case 8'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'special_offer_id' => 1,
                        'group_id'         => null,
                        'country_id'       => null,
                        'min_qty'          => 1,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                ],
            ],
            // Pricing rule applies to special offer case (by country) and has higher percentage
            // -> keep pricing rule
            'test case 9'  => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 10, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 1,
                        'group_id'   => null,
                        'country_id' => null,
                        'min_qty'    => 1,
                        'percent'    => 20,
                        'net_price'  => 80,
                        'details'    => [
                            'pricing' => 20,
                        ],
                    ],
                ],
            ],
            // Pricing rule applies to special offer case (by country) but has lower percentage
            // -> keep both
            'test case 10' => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'special_offer_id' => 1,
                        'group_id'         => null,
                        'country_id'       => 1,
                        'min_qty'          => 1,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                    [
                        'pricing_id' => 1,
                        'group_id'   => null,
                        'country_id' => null,
                        'min_qty'    => 1,
                        'percent'    => 10,
                        'net_price'  => 90,
                        'details'    => [
                            'pricing' => 10,
                        ],
                    ],
                ],
            ],
            // Distinct case, special offer has higher percentage
            // -> keep both
            'test case 11' => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 1,
                        'group_id'   => 1,
                        'country_id' => null,
                        'min_qty'    => 1,
                        'percent'    => 10,
                        'net_price'  => 90,
                        'details'    => [
                            'pricing' => 10,
                        ],
                    ],
                    [
                        'special_offer_id' => 1,
                        'group_id'         => null,
                        'country_id'       => 1,
                        'min_qty'          => 1,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                ],
            ],

            // Common case, special offer replace the 2 last pricing rules
            'test case 12' => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 10],
                    ['pricing_id' => 2, 'group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 12],
                    ['pricing_id' => 3, 'group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 15],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 15, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'special_offer_id' => 1,
                        'group_id'         => 1,
                        'country_id'       => null,
                        'min_qty'          => 15,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                    [
                        'pricing_id' => 1,
                        'group_id'   => 1,
                        'country_id' => null,
                        'min_qty'    => 10,
                        'percent'    => 10,
                        'net_price'  => 90,
                        'details'    => [
                            'pricing' => 10,
                        ],
                    ],
                ],
            ],

            // Special offer replaces pricing rules for group 1
            'test case 13' => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 10],
                    ['pricing_id' => 2, 'group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 12],
                    ['pricing_id' => 3, 'group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 15],
                    ['pricing_id' => 4, 'group_id' => 2, 'country_id' => null, 'min_qty' => 10, 'percent' => 15],
                    ['pricing_id' => 5, 'group_id' => 2, 'country_id' => null, 'min_qty' => 20, 'percent' => 17],
                    ['pricing_id' => 6, 'group_id' => 2, 'country_id' => null, 'min_qty' => 30, 'percent' => 20],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20, 'stack' => 0],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 6,
                        'group_id'   => 2,
                        'country_id' => null,
                        'min_qty'    => 30,
                        'percent'    => 20,
                        'net_price'  => 80,
                        'details'    => [
                            'pricing' => 20,
                        ],
                    ],
                    [
                        'pricing_id' => 5,
                        'group_id'   => 2,
                        'country_id' => null,
                        'min_qty'    => 20,
                        'percent'    => 17,
                        'net_price'  => 83,
                        'details'    => [
                            'pricing' => 17,
                        ],
                    ],
                    [
                        'pricing_id' => 4,
                        'group_id'   => 2,
                        'country_id' => null,
                        'min_qty'    => 10,
                        'percent'    => 15,
                        'net_price'  => 85,
                        'details'    => [
                            'pricing' => 15,
                        ],
                    ],
                    [
                        'special_offer_id' => 1,
                        'group_id'         => 1,
                        'country_id'       => null,
                        'min_qty'          => 1,
                        'percent'          => 20,
                        'net_price'        => 80,
                        'details'          => [
                            'special' => 20,
                        ],
                    ],
                ],
            ],

            // Special offer stacks over the 2 last pricing rules for group 1, and create a new rule for 15 min qty
            'test case 14' => [
                'pricing'       => [
                    ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 9],
                    ['pricing_id' => 2, 'group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 11],
                    ['pricing_id' => 3, 'group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 13],

                    ['pricing_id' => 4, 'group_id' => 2, 'country_id' => null, 'min_qty' => 10, 'percent' => 15],
                    ['pricing_id' => 5, 'group_id' => 2, 'country_id' => null, 'min_qty' => 20, 'percent' => 17],
                    ['pricing_id' => 6, 'group_id' => 2, 'country_id' => null, 'min_qty' => 30, 'percent' => 19],
                ],
                'special_offer' => [
                    ['special_offer_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 15, 'percent' => 10, 'stack' => 1],
                ],
                'offers'        => [
                    [
                        'pricing_id' => 6,
                        'group_id'   => 2,
                        'country_id' => null,
                        'min_qty'    => 30,
                        'percent'    => 19,
                        'net_price'  => 81,
                        'details'    => [
                            'pricing' => 19,
                        ],
                    ],
                    [
                        'pricing_id' => 5,
                        'group_id'   => 2,
                        'country_id' => null,
                        'min_qty'    => 20,
                        'percent'    => 17,
                        'net_price'  => 83,
                        'details'    => [
                            'pricing' => 17,
                        ],
                    ],
                    [
                        'pricing_id' => 4,
                        'group_id'   => 2,
                        'country_id' => null,
                        'min_qty'    => 10,
                        'percent'    => 15,
                        'net_price'  => 85,
                        'details'    => [
                            'pricing' => 15,
                        ],
                    ],
                    [
                        'pricing_id'       => 3,
                        'special_offer_id' => 1,
                        'group_id'         => 1,
                        'country_id'       => null,
                        'min_qty'          => 30,
                        'percent'          => 21.7,
                        'net_price'        => 78.3,
                        'details'          => [
                            'pricing' => 13,
                            'special' => 10,
                        ],
                    ],
                    [
                        'pricing_id'       => 2,
                        'special_offer_id' => 1,
                        'group_id'         => 1,
                        'country_id'       => null,
                        'min_qty'          => 20,
                        'percent'          => 19.9,
                        'net_price'        => 80.1,
                        'details'          => [
                            'pricing' => 11,
                            'special' => 10,
                        ],
                    ],
                    [
                        'special_offer_id' => 1,
                        'group_id'         => 1,
                        'country_id'       => null,
                        'min_qty'          => 15,
                        'percent'          => 10,
                        'net_price'        => 90,
                        'details'          => [
                            'special' => 10,
                        ],
                    ],
                    [
                        'pricing_id' => 1,
                        'group_id'   => 1,
                        'country_id' => null,
                        'min_qty'    => 10,
                        'percent'    => 9,
                        'net_price'  => 91,
                        'details'    => [
                            'pricing' => 9,
                        ],
                    ],
                ],
            ],
        ];
    }
}
