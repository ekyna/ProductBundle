<?php

declare(strict_types=1);

use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;

return [
    ProductInterface::class          => [
        'product_hawk' => [
            '__factory'            => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'                => '@brand_acme',
            'categories'           => [
                0 => '@category_component',
            ],
            'designation'          => 'Hand work',
            'reference'            => 'HAWK',
            'netPrice'             => "<decimal('30')>",
            'weight'               => "<decimal('0.0')>",
            'stockMode'            => 'disabled',
            'minimumOrderQuantity' => "<decimal('0.1')>",
            'taxGroup'             => '<defaultTaxGroup()>',
            'title'                => 'Hand work',
            'description'          => '<htmlParagraphs()>',
            'visible'              => false,
        ],
        'product_nfcr' => [
            '__factory'   => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'       => '@brand_acme',
            'categories'  => [
                0 => '@category_component',
            ],
            'designation' => 'NFC Reader',
            'reference'   => 'NFCR',
            'netPrice'    => "<decimal('20')>",
            'weight'      => "<decimal('0.2')>",
            'taxGroup'    => '<defaultTaxGroup()>',
            'title'       => 'NFC Reader',
            'description' => '<htmlParagraphs()>',
            'visible'     => false,
        ],
        'product_musb' => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_component',
            ],
            'attributeSet' => '@attribute_set_connector',
            'designation'  => 'Micro USB Connector',
            'reference'    => 'MUSB',
            'netPrice'     => "<decimal('10')>",
            'weight'       => "<decimal('0.05')>",
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Micro USB Connector',
            'description'  => '<htmlParagraphs()>',
            'visible'      => false,
        ],
        'product_typc' => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_component',
            ],
            'attributeSet' => '@attribute_set_connector',
            'designation'  => 'Type-C Connector',
            'reference'    => 'TYPC',
            'netPrice'     => "<decimal('10')>",
            'weight'       => "<decimal('0.05')>",
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Type-C Connector',
            'description'  => '<htmlParagraphs()>',
            'visible'      => false,
        ],
        'product_hub'  => [
            '__factory'   => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'       => '@brand_acme',
            'categories'  => [
                0 => '@category_component',
            ],
            'designation' => 'Hub',
            'reference'   => 'HUB',
            'netPrice'    => "<decimal('60')>",
            'weight'      => "<decimal('0.15')>",
            'taxGroup'    => '<defaultTaxGroup()>',
            'title'       => 'Hub',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
        'product_bk9'  => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_component',
            ],
            'attributeSet' => '@attribute_set_blocking_kit',
            'designation'  => 'Blocking kit 9 inches',
            'reference'    => 'BK9',
            'netPrice'     => "<decimal('10')>",
            'weight'       => "<decimal('0.05')>",
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Blocking kit 9 inches',
            'description'  => '<htmlParagraphs()>',
            'visible'      => false,
        ],
        'product_bk11' => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_blocking_kit',
            'designation'  => 'Blocking kit 11 inches',
            'reference'    => 'BK11',
            'netPrice'     => "<decimal('10')>",
            'weight'       => "<decimal('0.05')>",
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Blocking kit 11 inches',
            'description'  => '<htmlParagraphs()>',
            'visible'      => false,
        ],
        'product_taba' => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_SIMPLE],
            ],
            'brand'        => '@brand_samsung',
            'categories'   => [
                0 => '@category_device',
            ],
            'attributeSet' => '@attribute_set_device',
            'designation'  => 'Tablet A',
            'reference'    => 'TABA',
            'netPrice'     => "<decimal('240')>",
            'weight'       => "<decimal('0.6')>",
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Tablet A',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
    ],
    ProductAttributeInterface::class => [
        'product_musb_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_musb',
            'attributeSlot' => '@attribute_slot_connector_connector',
            'choices'       => [
                0 => '@attribute_choice_micro_usb',
            ],
        ],
        'product_typc_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_typc',
            'attributeSlot' => '@attribute_slot_connector_connector',
            'choices'       => [
                0 => '@attribute_choice_type_c',
            ],
        ],
        'product_bk9_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_bk9',
            'attributeSlot' => '@attribute_slot_blocking_kit_diagonal',
            'value'         => 9,
        ],
        'product_bk11_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_bk11',
            'attributeSlot' => '@attribute_slot_blocking_kit_diagonal',
            'value'         => 11,
        ],
        'product_taba_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_taba',
            'attributeSlot' => '@attribute_slot_device_network',
            'choices'       => [
                0 => '@attribute_choice_wifi',
            ],
        ],
        'product_taba_attribute_2' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_taba',
            'attributeSlot' => '@attribute_slot_device_capacity',
            'choices'       => [
                0 => '@attribute_choice_16go',
            ],
        ],
        'product_taba_attribute_3' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_taba',
            'attributeSlot' => '@attribute_slot_device_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_taba_attribute_4' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_taba',
            'attributeSlot' => '@attribute_slot_device_diagonal',
            'value'         => 9,
        ],
        'product_taba_attribute_5' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_taba',
            'attributeSlot' => '@attribute_slot_device_connector',
            'choices'       => [
                0 => '@attribute_choice_micro_usb',
            ],
        ],
    ],
    OptionGroupInterface::class      => [
        'product_hub_group_connector' => [
            '__factory' => [
                '@ekyna_product.factory.option_group::create' => [],
            ],
            'product'   => '@product_hub',
            'name'      => 'Connector',
            'title'     => 'Connector',
            'required'  => true,
            'fullTitle' => true,
        ],
    ],
    OptionInterface::class           => [
        'product_hub_group_connector_option_musb' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_hub_group_connector',
            'product'   => '@product_musb',
        ],
        'product_hub_group_connector_option_typc' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_hub_group_connector',
            'product'   => '@product_typc',
        ],
    ],
];
