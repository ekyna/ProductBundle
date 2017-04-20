<?php

declare(strict_types=1);

use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\CategoryInterface;

return [
    BrandInterface::class    => [
        'brand_apple'   => [
            '__factory'   => [
                '@ekyna_product.factory.brand::create' => [],
            ],
            'name'        => 'Apple',
            'title'       => 'Apple',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
        'brand_samsung' => [
            '__factory'   => [
                '@ekyna_product.factory.brand::create' => [],
            ],
            'name'        => 'Samsung',
            'title'       => 'Samsung',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
        'brand_acme'    => [
            '__factory'   => [
                '@ekyna_product.factory.brand::create' => [],
            ],
            'name'        => 'Acme',
            'title'       => 'Acme',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
    ],
    CategoryInterface::class => [
        'category_device'    => [
            '__factory'   => [
                '@ekyna_product.factory.category::create' => [],
            ],
            'name'        => 'Devices',
            'title'       => 'Devices',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
        'category_kiosk'     => [
            '__factory'   => [
                '@ekyna_product.factory.category::create' => [],
            ],
            'name'        => 'Kiosks',
            'title'       => 'Kiosks',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
        'category_case'      => [
            '__factory'   => [
                '@ekyna_product.factory.category::create' => [],
            ],
            'name'        => 'Cases',
            'title'       => 'Cases',
            'description' => '<htmlParagraphs()>',
            'visible'     => true,
        ],
        'category_component' => [
            '__factory'   => [
                '@ekyna_product.factory.category::create' => [],
            ],
            'name'        => 'Components',
            'title'       => 'Components',
            'description' => '<htmlParagraphs()>',
            'visible'     => false,
        ],
    ],
];
