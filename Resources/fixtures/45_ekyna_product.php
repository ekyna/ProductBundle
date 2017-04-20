<?php

declare(strict_types=1);

use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;

return [
    ProductInterface::class      => [
        'product_nrke' => [
            '__factory'   => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_BUNDLE],
            ],
            'brand'       => '@brand_acme',
            'categories'  => [
                0 => '@category_kiosk',
            ],
            'designation' => 'NFC Reader Kiosk Extension',
            'reference'   => 'NRKE',
            'taxGroup'    => '<defaultTaxGroup()>',
            'title'       => 'NFC Reader Kiosk Extension',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
        'product_kico' => [
            '__factory'   => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_CONFIGURABLE],
            ],
            'brand'       => '@brand_acme',
            'categories'  => [
                0 => '@category_kiosk',
            ],
            'designation' => 'Kiosk configurator',
            'reference'   => 'KICO',
            'taxGroup'    => '<defaultTaxGroup()>',
            'title'       => 'Kiosk configurator',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
    ],
    BundleSlotInterface::class   => [
        'product_nrke_slot_1' => [
            '__factory' => [
                '@ekyna_product.factory.bundle_slot::create' => [],
            ],
            'bundle'    => '@product_nrke',
        ],
        'product_nrke_slot_2' => [
            '__factory' => [
                '@ekyna_product.factory.bundle_slot::create' => [],
            ],
            'bundle'    => '@product_nrke',
        ],
        'product_kico_slot_1' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_slot::create' => [],
            ],
            'bundle'      => '@product_kico',
            'title'       => 'Kiosk Shell',
            'description' => '<htmlParagraphs()>',
        ],
        'product_kico_slot_2' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_slot::create' => [],
            ],
            'bundle'      => '@product_kico',
            'title'       => 'Kisok Base',
            'description' => '<htmlParagraphs()>',
        ],
        'product_kico_slot_3' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_slot::create' => [],
            ],
            'bundle'      => '@product_kico',
            'title'       => 'Extension',
            'description' => '<htmlParagraphs()>',
            'required'    => false,
        ],
    ],
    BundleChoiceInterface::class => [
        'product_nrke_slot_1_choice_1' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_choice::create' => [],
            ],
            'slot'        => '@product_nrke_slot_1',
            'product'     => '@product_nfcr',
            'minQuantity' => "<decimal('1')>",
        ],
        'product_nrke_slot_2_choice_1' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_choice::create' => [],
            ],
            'slot'        => '@product_nrke_slot_2',
            'product'     => '@product_hub',
            'minQuantity' => "<decimal('1')>",
        ],
        'product_kico_slot_1_choice_1' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_choice::create' => [],
            ],
            'slot'        => '@product_kico_slot_1',
            'product'     => '@product_sha',
            'minQuantity' => "<decimal('1')>",
            'maxQuantity' => "<decimal('1')>",
        ],
        'product_kico_slot_1_choice_2' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_choice::create' => [],
            ],
            'slot'        => '@product_kico_slot_1',
            'product'     => '@product_shb',
            'minQuantity' => "<decimal('1')>",
            'maxQuantity' => "<decimal('1')>",
        ],
        'product_kico_slot_2_choice_1' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_choice::create' => [],
            ],
            'slot'        => '@product_kico_slot_2',
            'product'     => '@product_kdb',
            'minQuantity' => "<decimal('1')>",
            'maxQuantity' => "<decimal('1')>",
        ],
        'product_kico_slot_2_choice_2' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_choice::create' => [],
            ],
            'slot'        => '@product_kico_slot_2',
            'product'     => '@product_ksb',
            'minQuantity' => "<decimal('1')>",
            'maxQuantity' => "<decimal('1')>",
        ],
        'product_kico_slot_3_choice_1' => [
            '__factory'   => [
                '@ekyna_product.factory.bundle_choice::create' => [],
            ],
            'slot'        => '@product_kico_slot_3',
            'product'     => '@product_nrke',
            'minQuantity' => "<decimal('1')>",
            'maxQuantity' => "<decimal('1')>",
        ],
    ],
    OptionGroupInterface::class  => [
        'product_nrke_group_1' => [
            '__factory' => [
                '@ekyna_product.factory.option_group::create' => [],
            ],
            'product'   => '@product_nrke',
            'name'      => 'Shell',
            'title'     => 'Shell',
            'required'  => false,
            'fullTitle' => true,
        ],
        'product_kico_group_1' => [
            '__factory' => [
                '@ekyna_product.factory.option_group::create' => [],
            ],
            'product'   => '@product_kico',
            'name'      => 'Assembly',
            'title'     => 'Assembly',
            'required'  => false,
            'fullTitle' => true,
        ],
    ],
    OptionInterface::class       => [
        'product_nrke_group_1_option_1' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_nrke_group_1',
            'product'   => '@product_kesw',
        ],
        'product_nrke_group_1_option_2' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_nrke_group_1',
            'product'   => '@product_kesb',
        ],
        'product_kico_group_1_option_1' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_kico_group_1',
            'product'   => '@product_hawk',
        ],
    ],
];

