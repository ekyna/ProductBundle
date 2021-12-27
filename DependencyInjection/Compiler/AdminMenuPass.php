<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    public const GROUP = [
        'name'     => 'catalog',
        'label'    => 'label',
        'domain'   => 'EkynaProduct',
        'icon'     => 'cube',
        'position' => 12,
    ];

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        // CATALOG

        $pool->addMethodCall('createGroup', [self::GROUP]);

        // Products
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'products',
                'resource' => 'ekyna_product.product',
                'position' => 1,
            ],
        ]);

        // Categories
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'categories',
                'resource' => 'ekyna_product.category',
                'position' => 2,
            ],
        ]);

        // Brands
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'brands',
                'resource' => 'ekyna_product.brand',
                'position' => 3,
            ],
        ]);

        // Attribute sets
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'attribute_sets',
                'resource' => 'ekyna_product.attribute_set',
                'position' => 10,
            ],
        ]);

        // Attributes
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'attributes',
                'resource' => 'ekyna_product.attribute',
                'position' => 11,
            ],
        ]);

        // Pricing
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'pricing',
                'resource' => 'ekyna_product.pricing',
                'position' => 70,
            ],
        ]);

        // Special offers
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'special_offer',
                'resource' => 'ekyna_product.special_offer',
                'position' => 71,
            ],
        ]);

        // Inventory
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'inventory',
                'route'    => 'admin_ekyna_product_inventory_index',
                'label'    => 'inventory.title',
                'domain'   => 'EkynaProduct',
                'resource' => 'ekyna_product.product',
                'position' => 90,
            ],
        ]);

        // Highlight
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'highlight',
                'route'    => 'admin_ekyna_product_highlight_index',
                'label'    => 'highlight.title',
                'domain'   => 'EkynaProduct',
                'resource' => 'ekyna_product.product',
                'position' => 91,
            ],
        ]);

        if (!$container->getParameter('ekyna_product.catalog_enabled')) {
            return;
        }

        // Catalog
        $pool->addMethodCall('createEntry', [
            'catalog',
            [
                'name'     => 'catalog',
                'resource' => 'ekyna_product.catalog',
                'position' => 80,
            ],
        ]);
    }
}
