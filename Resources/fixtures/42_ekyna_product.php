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
            '__factory'      => [
                '@ekyna_product.factory.pricing::create' => [],
            ],
            'designation'    => 'Tarifs revendeur',
            'pricingGroups'  => ["<resource('ekyna_product.pricing_group', '{id: 1}')>",],
            'customerGroups' => ['<customerGroup(true)>',],
            'countries'      => ['<countryByCode(\'FR\')>',],
            'brands'         => ['@brand_acme',],
            'rules'          => [
                '@pricing_1_rule_1',
                '@pricing_1_rule_2',
                '@pricing_1_rule_3',
                '@pricing_1_rule_4',
                '@pricing_1_rule_5',
            ],
        ],
    ],
];
