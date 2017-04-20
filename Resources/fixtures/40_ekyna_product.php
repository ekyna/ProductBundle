<?php

declare(strict_types=1);

use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeSlotInterface;

return [
    AttributeInterface::class => [
        'attribute_color'     => [
            '__factory' => [
                '@ekyna_product.factory.attribute::create' => [],
            ],
            'name'      => 'Color',
            'title'     => 'Color',
            'type'      => 'select',
            'config'    => [
                'multiple' => false,
            ],
        ],
        'attribute_capacity'  => [
            '__factory' => [
                '@ekyna_product.factory.attribute::create' => [],
            ],
            'name'      => 'Capacity',
            'title'     => 'Capacity',
            'type'      => 'select',
            'config'    => [
                'multiple' => false,
            ],
        ],
        'attribute_network'   => [
            '__factory' => [
                '@ekyna_product.factory.attribute::create' => [],
            ],
            'name'      => 'Networks',
            'title'     => 'Networks',
            'type'      => 'select',
            'config'    => [
                'multiple' => true,
            ],
        ],
        'attribute_connector' => [
            '__factory' => [
                '@ekyna_product.factory.attribute::create' => [],
            ],
            'name'      => 'Connector',
            'title'     => 'Connector',
            'type'      => 'select',
            'config'    => [
                'multiple' => false,
            ],
        ],
        'attribute_diagonal'  => [
            '__factory' => [
                '@ekyna_product.factory.attribute::create' => [],
            ],
            'name'      => 'Diagonal',
            'title'     => 'Diagonal',
            'type'      => 'unit',
            'config'    => [
                'unit' => 'inch',
            ],
        ],
    ],
    AttributeChoiceInterface::class => [
        'attribute_choice_white'     => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_color',
            'name'      => 'White',
            'title'     => 'White',
        ],
        'attribute_choice_black'     => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_color',
            'name'      => 'Black',
            'title'     => 'Black',
        ],
        'attribute_choice_16go'      => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_capacity',
            'name'      => '16 Go',
            'title'     => '16 Go',
        ],
        'attribute_choice_32go'      => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_capacity',
            'name'      => '32 Go',
            'title'     => '32 Go',
        ],
        'attribute_choice_wifi'      => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_network',
            'name'      => 'WiFi',
            'title'     => 'WiFi',
        ],
        'attribute_choice_4g'        => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_network',
            'name'      => '4G',
            'title'     => '4G',
        ],
        'attribute_choice_micro_usb' => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_connector',
            'name'      => 'Micro USB',
            'title'     => 'Micro USB',
        ],
        'attribute_choice_type_c'    => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_connector',
            'name'      => 'Type-C',
            'title'     => 'Type-C',
        ],
        'attribute_choice_lightning' => [
            '__factory' => [
                '@ekyna_product.factory.attribute_choice::create' => [],
            ],
            'attribute' => '@attribute_connector',
            'name'      => 'Lightning',
            'title'     => 'Lightning',
        ],
    ],
    AttributeSetInterface::class    => [
        'attribute_set_device'       => [
            '__factory' => [
                '@ekyna_product.factory.attribute_set::create' => [],
            ],
            'name'      => 'Device',
        ],
        'attribute_set_color'        => [
            '__factory' => [
                '@ekyna_product.factory.attribute_set::create' => [],
            ],
            'name'      => 'Color',
        ],
        'attribute_set_connector'    => [
            '__factory' => [
                '@ekyna_product.factory.attribute_set::create' => [],
            ],
            'name'      => 'Connector',
        ],
        'attribute_set_kiosk'        => [
            '__factory' => [
                '@ekyna_product.factory.attribute_set::create' => [],
            ],
            'name'      => 'Kisok',
        ],
        'attribute_set_blocking_kit' => [
            '__factory' => [
                '@ekyna_product.factory.attribute_set::create' => [],
            ],
            'name'      => 'Blocking Kit',
        ],
    ],
    AttributeSlotInterface::class   => [
        'attribute_slot_device_network'        => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_device',
            'attribute' => '@attribute_network',
            'required'  => true,
            'naming'    => true,
        ],
        'attribute_slot_device_capacity'       => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_device',
            'attribute' => '@attribute_capacity',
            'required'  => true,
            'naming'    => true,
        ],
        'attribute_slot_device_color'          => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_device',
            'attribute' => '@attribute_color',
            'required'  => true,
            'naming'    => true,
        ],
        'attribute_slot_device_diagonal'       => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_device',
            'attribute' => '@attribute_diagonal',
            'required'  => true,
        ],
        'attribute_slot_device_connector'      => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_device',
            'attribute' => '@attribute_connector',
            'required'  => true,
        ],
        'attribute_slot_color_color'           => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_color',
            'attribute' => '@attribute_color',
            'required'  => true,
            'naming'    => true,
        ],
        'attribute_slot_connector_connector'   => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_connector',
            'attribute' => '@attribute_connector',
            'required'  => true,
        ],
        'attribute_slot_kiosk_diagonal'        => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_kiosk',
            'attribute' => '@attribute_diagonal',
            'required'  => true,
            'naming'    => true,
        ],
        'attribute_slot_kiosk_color'           => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_kiosk',
            'attribute' => '@attribute_color',
            'required'  => true,
            'naming'    => true,
        ],
        'attribute_slot_blocking_kit_diagonal' => [
            '__factory' => [
                '@ekyna_product.factory.attribute_slot::create' => [],
            ],
            'set'       => '@attribute_set_blocking_kit',
            'attribute' => '@attribute_diagonal',
            'required'  => true,
        ],
    ],
];
