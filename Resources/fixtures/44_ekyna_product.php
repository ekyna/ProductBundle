<?php

declare(strict_types=1);

use Ekyna\Bundle\ProductBundle\Model\ComponentInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;

return [
    ProductInterface::class          => [
        'product_tabb'  => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_samsung',
            'categories'   => [
                0 => '@category_device',
            ],
            'attributeSet' => '@attribute_set_device',
            'designation'  => 'Tablet B',
            'reference'    => 'TABB',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Tablet B',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_tabbw' => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_tabb',
            'reference' => 'TABB-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('300')>",
            'weight'    => "<decimal('0.8')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_tabbb' => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_tabb',
            'reference' => 'TABB-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('340')>",
            'weight'    => "<decimal('0.8')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_tabc'  => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_apple',
            'categories'   => [
                0 => '@category_device',
            ],
            'attributeSet' => '@attribute_set_device',
            'designation'  => 'Tablet C',
            'reference'    => 'TABC',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Tablet C',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_tabcw' => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_tabc',
            'reference' => 'TABC-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('320')>",
            'weight'    => "<decimal('0.8')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_tabcb' => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_tabc',
            'reference' => 'TABC-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('360')>",
            'weight'    => "<decimal('0.8')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_kes'   => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_color',
            'designation'  => 'Kiosk Extension Shell',
            'reference'    => 'KES',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Kiosk Extension Shell',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_kesw'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_kes',
            'reference' => 'KES-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('42')>",
            'weight'    => "<decimal('0.4')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_kesb'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_kes',
            'reference' => 'KES-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('40')>",
            'weight'    => "<decimal('0.4')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_swc'   => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_color',
            'designation'  => 'Switch Cuff',
            'reference'    => 'SWC',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Switch Cuff',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_swcw'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_swc',
            'reference' => 'SWC-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('12')>",
            'weight'    => "<decimal('0.1')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_swcb'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_swc',
            'reference' => 'SWC-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('10')>",
            'weight'    => "<decimal('0.1')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_roc'   => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_color',
            'designation'  => 'Rotate Cuff',
            'reference'    => 'ROC',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Rotate Cuff',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_rocw'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_roc',
            'reference' => 'ROC-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('14')>",
            'weight'    => "<decimal('0.1')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_rocb'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_roc',
            'reference' => 'ROC-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('12')>",
            'weight'    => "<decimal('0.1')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_kdb'   => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_color',
            'designation'  => 'Kiosk Desk Base',
            'reference'    => 'KDB',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Kiosk Desk Base',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_kdbw'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_kdb',
            'reference' => 'KDB-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('52')>",
            'weight'    => "<decimal('0.4')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_kdbb'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_kdb',
            'reference' => 'KDB-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('50')>",
            'weight'    => "<decimal('0.4')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_ksb'   => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_color',
            'designation'  => 'Kiosk Stand Base',
            'reference'    => 'KSB',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Kiosk Stand Base',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_ksbw'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_ksb',
            'reference' => 'KSB-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('62')>",
            'weight'    => "<decimal('0.5')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_ksbb'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_ksb',
            'reference' => 'KSB-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('60')>",
            'weight'    => "<decimal('0.5')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_sha'   => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_kiosk',
            'designation'  => 'Kiosk Shell A',
            'reference'    => 'SHA',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Kiosk Shell A',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_shaw'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_sha',
            'reference' => 'SHA-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('48')>",
            'weight'    => "<decimal('0.35')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_shab'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_sha',
            'reference' => 'SHA-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('45')>",
            'weight'    => "<decimal('0.35')>",
            'visible'   => true,
            'position'  => 1,
        ],
        'product_shb'   => [
            '__factory'    => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIABLE],
            ],
            'brand'        => '@brand_acme',
            'categories'   => [
                0 => '@category_kiosk',
            ],
            'attributeSet' => '@attribute_set_kiosk',
            'designation'  => 'Kiosk Shell B',
            'reference'    => 'SHB',
            'taxGroup'     => '<defaultTaxGroup()>',
            'title'        => 'Kiosk Shell B',
            'description'  => '<htmlParagraphs()>',
            'visible'      => true,
        ],
        'product_shbw'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_shb',
            'reference' => 'SHB-W',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('62')>",
            'weight'    => "<decimal('0.35')>",
            'visible'   => true,
            'position'  => 0,
        ],
        'product_shbb'  => [
            '__factory' => [
                '@ekyna_product.factory.product::createWithType' => [ProductTypes::TYPE_VARIANT],
            ],
            'parent'    => '@product_shb',
            'reference' => 'SHB-B',
            'taxGroup'     => '<defaultTaxGroup()>',
            'netPrice'  => "<decimal('60')>",
            'weight'    => "<decimal('0.35')>",
            'visible'   => true,
            'position'  => 1,
        ],
    ],
    ComponentInterface::class        => [
        'product_sha_component_1' => [
            '__factory' => [
                '@ekyna_product.factory.component::create' => [],
            ],
            'parent'    => '@product_sha',
            'child'     => '@product_bk9',
        ],
        'product_shb_component_1' => [
            '__factory' => [
                '@ekyna_product.factory.component::create' => [],
            ],
            'parent'    => '@product_shb',
            'child'     => '@product_bk11',
        ],
    ],
    ProductAttributeInterface::class => [
        'product_tabbw_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbw',
            'attributeSlot' => '@attribute_slot_device_network',
            'choices'       => [
                0 => '@attribute_choice_wifi',
            ],
        ],
        'product_tabbw_attribute_2' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbw',
            'attributeSlot' => '@attribute_slot_device_capacity',
            'choices'       => [
                0 => '@attribute_choice_16go',
            ],
        ],
        'product_tabbw_attribute_3' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbw',
            'attributeSlot' => '@attribute_slot_device_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_tabbw_attribute_4' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbw',
            'attributeSlot' => '@attribute_slot_device_diagonal',
            'value'         => 11,
        ],
        'product_tabbw_attribute_5' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbw',
            'attributeSlot' => '@attribute_slot_device_connector',
            'choices'       => [
                0 => '@attribute_choice_type_c',
            ],
        ],
        'product_tabbb_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbb',
            'attributeSlot' => '@attribute_slot_device_network',
            'choices'       => [
                0 => '@attribute_choice_wifi',
                1 => '@attribute_choice_4g',
            ],
        ],
        'product_tabbb_attribute_2' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbb',
            'attributeSlot' => '@attribute_slot_device_capacity',
            'choices'       => [
                0 => '@attribute_choice_32go',
            ],
        ],
        'product_tabbb_attribute_3' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbb',
            'attributeSlot' => '@attribute_slot_device_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_tabbb_attribute_4' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbb',
            'attributeSlot' => '@attribute_slot_device_diagonal',
            'value'         => 11,
        ],
        'product_tabbb_attribute_5' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabbb',
            'attributeSlot' => '@attribute_slot_device_connector',
            'choices'       => [
                0 => '@attribute_choice_type_c',
            ],
        ],
        'product_tabcw_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcw',
            'attributeSlot' => '@attribute_slot_device_network',
            'choices'       => [
                0 => '@attribute_choice_wifi',
            ],
        ],
        'product_tabcw_attribute_2' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcw',
            'attributeSlot' => '@attribute_slot_device_capacity',
            'choices'       => [
                0 => '@attribute_choice_16go',
            ],
        ],
        'product_tabcw_attribute_3' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcw',
            'attributeSlot' => '@attribute_slot_device_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_tabcw_attribute_4' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcw',
            'attributeSlot' => '@attribute_slot_device_diagonal',
            'value'         => 11,
        ],
        'product_tabcw_attribute_5' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcw',
            'attributeSlot' => '@attribute_slot_device_connector',
            'choices'       => [
                0 => '@attribute_choice_lightning',
            ],
        ],
        'product_tabcb_attribute_1' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcb',
            'attributeSlot' => '@attribute_slot_device_network',
            'choices'       => [
                0 => '@attribute_choice_wifi',
                1 => '@attribute_choice_4g',
            ],
        ],
        'product_tabcb_attribute_2' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcb',
            'attributeSlot' => '@attribute_slot_device_capacity',
            'choices'       => [
                0 => '@attribute_choice_32go',
            ],
        ],
        'product_tabcb_attribute_3' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcb',
            'attributeSlot' => '@attribute_slot_device_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_tabcb_attribute_4' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcb',
            'attributeSlot' => '@attribute_slot_device_diagonal',
            'value'         => 11,
        ],
        'product_tabcb_attribute_5' => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_tabcb',
            'attributeSlot' => '@attribute_slot_device_connector',
            'choices'       => [
                0 => '@attribute_choice_lightning',
            ],
        ],
        'product_kesw_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_kesw',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_kesb_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_kesb',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_swcw_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_swcw',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_swcb_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_swcb',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_rocw_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_rocw',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_rocb_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_rocb',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_kdbw_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_kdbw',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_kdbb_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_kdbb',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_ksbw_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_ksbw',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_ksbb_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_ksbb',
            'attributeSlot' => '@attribute_slot_color_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_shaw_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shaw',
            'attributeSlot' => '@attribute_slot_kiosk_diagonal',
            'value'         => 9,
        ],
        'product_shaw_attribute_2'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shaw',
            'attributeSlot' => '@attribute_slot_kiosk_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_shab_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shab',
            'attributeSlot' => '@attribute_slot_kiosk_diagonal',
            'value'         => 9,
        ],
        'product_shab_attribute_2'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shab',
            'attributeSlot' => '@attribute_slot_kiosk_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
        'product_shbw_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shbw',
            'attributeSlot' => '@attribute_slot_kiosk_diagonal',
            'value'         => 9,
        ],
        'product_shbw_attribute_2'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shbw',
            'attributeSlot' => '@attribute_slot_kiosk_color',
            'choices'       => [
                0 => '@attribute_choice_white',
            ],
        ],
        'product_shbb_attribute_1'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shbb',
            'attributeSlot' => '@attribute_slot_kiosk_diagonal',
            'value'         => 9,
        ],
        'product_shbb_attribute_2'  => [
            '__factory'     => [
                '@ekyna_product.factory.product_attribute::create' => [],
            ],
            'product'       => '@product_shbb',
            'attributeSlot' => '@attribute_slot_kiosk_color',
            'choices'       => [
                0 => '@attribute_choice_black',
            ],
        ],
    ],
    OptionGroupInterface::class      => [
        'product_shbw_group_1' => [
            '__factory' => [
                '@ekyna_product.factory.option_group::create' => [],
            ],
            'product'   => '@product_shbw',
            'name'      => 'Cuff',
            'title'     => 'Cuff',
            'required'  => false,
            'fullTitle' => true,
        ],
        'product_shbb_group_1' => [
            '__factory' => [
                '@ekyna_product.factory.option_group::create' => [],
            ],
            'product'   => '@product_shbb',
            'name'      => 'Cuff',
            'title'     => 'Cuff',
            'required'  => false,
            'fullTitle' => true,
        ],
    ],
    OptionInterface::class           => [
        'product_shbw_group_1_option_1' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_shbw_group_1',
            'product'   => '@product_swcw',
        ],
        'product_shbw_group_1_option_2' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_shbw_group_1',
            'product'   => '@product_rocw',
        ],
        'product_shbb_group_1_option_1' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_shbb_group_1',
            'product'   => '@product_swcb',
        ],
        'product_shbb_group_1_option_2' => [
            '__factory' => [
                '@ekyna_product.factory.option::create' => [],
            ],
            'group'     => '@product_shbb_group_1',
            'product'   => '@product_rocb',
        ],
    ],
];

