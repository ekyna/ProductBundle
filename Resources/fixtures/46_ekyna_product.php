<?php

declare(strict_types=1);

use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Entity\CatalogSlot;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;

return [
    SpecialOfferInterface::class       => [
        'special_offer_1' => [
            '__factory' => [
                '@ekyna_product.factory.special_offer::create' => [],
            ],
            'name' => 'Remise Acme Entreprise',
            'percent' => "<decimal('10')>",
            'enabled' => true,
            'brands'     => ['@brand_acme'],
            'groups'     => ["<resource('ekyna_commerce.customer_group', '{id: 2}')>",],
        ],
    ],
    CatalogInterface::class => [
        'catalog_1' => [
            '__factory' => [
                '@ekyna_product.factory.catalog::create' => [],
            ],
            'theme'     => 'default',
            'title'     => 'Dummy Catalog',
        ],
    ],
    CatalogPage::class      => [
        'catalog_1_page_1' => [
            'catalog'  => '@catalog_1',
            'number'   => 0,
            'template' => 'default.full',
        ],
    ],
    CatalogSlot::class      => [
        'catalog_1_page_1_slot_1' => [
            'page' => '@catalog_1_page_1',
            'product' => '@product_nfcr',
            'number'   => 0,
        ],
    ],
];
