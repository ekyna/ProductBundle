<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Tests\Service\Pricing;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferResolver;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * Class OfferResolverTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Service\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferResolverTest extends TestCase
{
    private ?OfferResolver $resolver;
    private ?MockObject $pricingRepository;
    private ?MockObject $specialOfferRepository;
    private ?MockObject $priceCalculator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->pricingRepository = $this->createMock(Repository\PricingRepository::class);
        $this->specialOfferRepository = $this->createMock(Repository\SpecialOfferRepository::class);
        $this->priceCalculator = $this->createMock(PriceCalculator::class);
        $this->resolver = new OfferResolver(
            $this->pricingRepository,
            $this->specialOfferRepository,
            $this->priceCalculator
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->pricingRepository = null;
        $this->specialOfferRepository = null;
        $this->priceCalculator = null;
        $this->resolver = null;
    }

    /**
     * @param array $pricing
     * @param array $specialOffer
     * @param array $expected
     *
     * @dataProvider resolveDataProvider
     */
    public function testResolve(array $pricing, array $specialOffer, array $expected): void
    {
        $pricing = array_map([$this, 'normalizeOfferData'], $pricing);
        $specialOffer = array_map([$this, 'normalizeOfferData'], $specialOffer);
        $expected = array_map([$this, 'normalizeOfferData'], $expected);

        /** @var Model\ProductInterface|MockObject $product */
        $product = $this->createMock(Model\ProductInterface::class);
        $product
            ->method('getMinPrice')
            ->willReturn(new Decimal(100));

        $this->pricingRepository
            ->method('findRulesByProduct')
            ->with($product)
            ->willReturn($pricing);

        $this->specialOfferRepository
            ->method('findRulesByProduct')
            ->with($product)
            ->willReturn($specialOffer);

        $result = $this->resolver->resolve($product);

        self::assertEquals($expected, $result);
    }

    private function normalizeOfferData(array $data): array
    {
        $data['min_qty'] = new Decimal($data['min_qty']);
        $data['percent'] = new Decimal($data['percent']);

        if (!isset($data['net_price'])) {
            return $data;
        }

        $data['net_price'] = new Decimal($data['net_price']);

        if (isset($data['details']['special'])) {
            $data['details']['special'] = new Decimal($data['details']['special']);
        }
        if (isset($data['details']['pricing'])) {
            $data['details']['pricing'] = new Decimal($data['details']['pricing']);
        }

        return $data;
    }

    /**
     * Provides test data for testResolve().
     */
    public function resolveDataProvider(): Generator
    {
        // TODO Components

        // Special offer has higher percentage than pricing rule
        // -> keep special offer
        yield 'test case 1'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Special offer has lower percentage than pricing rule
        // -> keep pricing rule
        yield 'test case 2'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 10,
                    'stack'            => 0,
                ],
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
        ];

        // Special offer applies to pricing rule case (by group) but has lower percentage
        // -> keep both
        yield 'test case 3'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 10,
                    'stack'            => 0,
                ],
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
        ];

        // Special offer applies to pricing rule case (by group) and has higher percentage
        // -> keep special offer
        yield 'test case 4'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Pricing rule applies to special offer case (by group) and has higher percentage
        // -> keep pricing rule
        yield 'test case 5'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => 1,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 10,
                    'stack'            => 0,
                ],
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
        ];

        // Pricing rule applies to special offer case (by group) but has lower percentage
        // -> keep both
        yield 'test case 6'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => 1,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Special offer applies to pricing rule case (by country) but has lower percentage
        // -> keep both
        yield 'test case 7'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 20],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 10,
                    'stack'            => 0,
                ],
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
        ];

        // Special offer applies to pricing rule case (by country) and has higher percentage
        // -> keep special offer
        yield 'test case 8'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => 1, 'min_qty' => 1, 'percent' => 10],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Pricing rule applies to special offer case (by country) and has higher percentage
        // -> keep pricing rule
        yield 'test case 9'  => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 20],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => 1,
                    'min_qty'          => 1,
                    'percent'          => 10,
                    'stack'            => 0,
                ],
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
        ];

        // Pricing rule applies to special offer case (by country) but has lower percentage
        // -> keep both
        yield 'test case 10' => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => null, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => 1,
                    'min_qty'          => 1,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Distinct case, special offer has higher percentage
        // -> keep both
        yield 'test case 11' => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 1, 'percent' => 10],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => null,
                    'country_id'       => 1,
                    'min_qty'          => 1,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Common case, special offer replace the 2 last pricing rules
        yield 'test case 12' => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 10],
                ['pricing_id' => 2, 'group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 12],
                ['pricing_id' => 3, 'group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 15],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => 1,
                    'country_id'       => null,
                    'min_qty'          => 15,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Special offer replaces pricing rules for group 1
        yield 'test case 13' => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 10],
                ['pricing_id' => 2, 'group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 12],
                ['pricing_id' => 3, 'group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 15],
                ['pricing_id' => 4, 'group_id' => 2, 'country_id' => null, 'min_qty' => 10, 'percent' => 15],
                ['pricing_id' => 5, 'group_id' => 2, 'country_id' => null, 'min_qty' => 20, 'percent' => 17],
                ['pricing_id' => 6, 'group_id' => 2, 'country_id' => null, 'min_qty' => 30, 'percent' => 20],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => 1,
                    'country_id'       => null,
                    'min_qty'          => 1,
                    'percent'          => 20,
                    'stack'            => 0,
                ],
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
        ];

        // Special offer stacks over the 2 last pricing rules for group 1, and create a new rule for 15 min qty
        yield 'test case 14' => [
            'pricing'       => [
                ['pricing_id' => 1, 'group_id' => 1, 'country_id' => null, 'min_qty' => 10, 'percent' => 9],
                ['pricing_id' => 2, 'group_id' => 1, 'country_id' => null, 'min_qty' => 20, 'percent' => 11],
                ['pricing_id' => 3, 'group_id' => 1, 'country_id' => null, 'min_qty' => 30, 'percent' => 13],

                ['pricing_id' => 4, 'group_id' => 2, 'country_id' => null, 'min_qty' => 10, 'percent' => 15],
                ['pricing_id' => 5, 'group_id' => 2, 'country_id' => null, 'min_qty' => 20, 'percent' => 17],
                ['pricing_id' => 6, 'group_id' => 2, 'country_id' => null, 'min_qty' => 30, 'percent' => 19],
            ],
            'special_offer' => [
                [
                    'special_offer_id' => 1,
                    'group_id'         => 1,
                    'country_id'       => null,
                    'min_qty'          => 15,
                    'percent'          => 10,
                    'stack'            => 1,
                ],
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
                    'percent'          => '21.7',
                    'net_price'        => '78.3',
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
                    'percent'          => '19.9',
                    'net_price'        => '80.1',
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
        ];
    }
}
