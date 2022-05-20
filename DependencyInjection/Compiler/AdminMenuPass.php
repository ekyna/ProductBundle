<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\AdminBundle\Service\Menu\PoolHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    private const NAME = 'catalog';

    public const GROUP = [
        'name'     => self::NAME,
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

        $helper = new PoolHelper(
            $container->getDefinition('ekyna_admin.menu.pool')
        );

        $helper
            ->addGroup(self::GROUP)
            ->addEntry([
                'name'     => 'products',
                'resource' => 'ekyna_product.product',
                'position' => 1,
            ])
            ->addEntry([
                'name'     => 'categories',
                'resource' => 'ekyna_product.category',
                'position' => 2,
            ])
            ->addEntry([
                'name'     => 'brands',
                'resource' => 'ekyna_product.brand',
                'position' => 3,
            ])
            ->addEntry([
                'name'     => 'attribute_sets',
                'resource' => 'ekyna_product.attribute_set',
                'position' => 10,
            ])
            ->addEntry([
                'name'     => 'attributes',
                'resource' => 'ekyna_product.attribute',
                'position' => 11,
            ])
            ->addEntry([
                'name'     => 'pricing',
                'resource' => 'ekyna_product.pricing',
                'position' => 70,
            ])
            ->addEntry([
                'name'     => 'special_offer',
                'resource' => 'ekyna_product.special_offer',
                'position' => 71,
            ])
            ->addEntry([
                'name'     => 'inventory',
                'route'    => 'admin_ekyna_product_inventory_index',
                'label'    => 'inventory.title',
                'domain'   => 'EkynaProduct',
                'resource' => 'ekyna_product.product',
                'position' => 90,
            ])
            ->addEntry([
                'name'     => 'highlight',
                'route'    => 'admin_ekyna_product_highlight_index',
                'label'    => 'highlight.title',
                'domain'   => 'EkynaProduct',
                'resource' => 'ekyna_product.product',
                'position' => 91,
            ]);

        if (!$container->getParameter('ekyna_product.catalog_enabled')) {
            return;
        }

        $helper->addEntry([
            'name'     => 'catalog',
            'resource' => 'ekyna_product.catalog',
            'position' => 80,
        ]);
    }
}
