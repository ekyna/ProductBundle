<?php

declare(strict_types=1);

use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingRuleInterface;

return [
    PricingRuleInterface::class => [
        'pricing_1_rule_1' => [
            '__factory'   => [
                '@ekyna_product.factory.pricing_rule::create' => [],
            ],
            'minQuantity' => "<decimal('1')>",
            'percent'     => "<decimal('20')>",
        ],
        'pricing_1_rule_2' => [
            '__factory'   => [
                '@ekyna_product.factory.pricing_rule::create' => [],
            ],
            'minQuantity' => "<decimal('20')>",
            'percent'     => "<decimal('22')>",
        ],
        'pricing_1_rule_3' => [
            '__factory'   => [
                '@ekyna_product.factory.pricing_rule::create' => [],
            ],
            'minQuantity' => "<decimal('50')>",
            'percent'     => "<decimal('25')>",
        ],
        'pricing_1_rule_4' => [
            '__factory'   => [
                '@ekyna_product.factory.pricing_rule::create' => [],
            ],
            'minQuantity' => "<decimal('100')>",
            'percent'     => "<decimal('30')>",
        ],
        'pricing_1_rule_5' => [
            '__factory'   => [
                '@ekyna_product.factory.pricing_rule::create' => [],
            ],
            'minQuantity' => "<decimal('500')>",
            'percent'     => "<decimal('32')>",
        ],
    ],
    PricingInterface::class     => [
        'pricing_1' => [
            '__factory'   => [
                '@ekyna_product.factory.pricing::create' => [],
            ],
            'designation' => 'Tarifs revendeur',
            'groups'      => [
                0 => '<customerGroup(true)>',
            ],
            'countries'   => [
                0 => '<countryByCode(\'FR\')>',
            ],
            'brands'      => [
                0 => '@brand_acme',
            ],
            'rules'       => [
                0 => '@pricing_1_rule_1',
                1 => '@pricing_1_rule_2',
                2 => '@pricing_1_rule_3',
                3 => '@pricing_1_rule_4',
                4 => '@pricing_1_rule_5',
            ],
        ],
    ],
];
