<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        // SALES

        $pool->addMethodCall('createGroup', [[
            'name'     => 'sales',
            'label'    => 'ekyna_commerce.sale.label.plural',
            'icon'     => 'file',
            'position' => 10,
        ]]);

        // Pricings
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'pricings',
            'route'    => 'ekyna_product_pricing_admin_home',
            'label'    => 'ekyna_product.pricing.label.plural',
            'resource' => 'ekyna_product_pricing',
            'position' => 99,
        ]]);

        // CATALOG

        $pool->addMethodCall('createGroup', [[
            'name'     => 'catalog',
            'label'    => 'ekyna_product.catalog',
            'icon'     => 'file',
            'position' => 12,
        ]]);

        // Products
        $pool->addMethodCall('createEntry', ['catalog', [
            'name'     => 'products',
            'route'    => 'ekyna_product_product_admin_home',
            'label'    => 'ekyna_product.product.label.plural',
            'resource' => 'ekyna_product_product',
            'position' => 1,
        ]]);

        // Categories
        $pool->addMethodCall('createEntry', ['catalog', [
            'name'     => 'categories',
            'route'    => 'ekyna_product_category_admin_home',
            'label'    => 'ekyna_product.category.label.plural',
            'resource' => 'ekyna_product_category',
            'position' => 2,
        ]]);

        // Brands
        $pool->addMethodCall('createEntry', ['catalog', [
            'name'     => 'brands',
            'route'    => 'ekyna_product_brand_admin_home',
            'label'    => 'ekyna_product.brand.label.plural',
            'resource' => 'ekyna_product_brand',
            'position' => 3,
        ]]);

        // Attribute sets
        $pool->addMethodCall('createEntry', ['catalog', [
            'name'     => 'attribute_sets',
            'route'    => 'ekyna_product_attribute_set_admin_home',
            'label'    => 'ekyna_product.attribute_set.label.plural',
            'resource' => 'ekyna_product_attribute_set',
            'position' => 10,
        ]]);

        // Attributes
        $pool->addMethodCall('createEntry', ['catalog', [
            'name'     => 'attribute_groups',
            'route'    => 'ekyna_product_attribute_group_admin_home',
            'label'    => 'ekyna_product.attribute_group.label.plural',
            'resource' => 'ekyna_product_attribute_group',
            'position' => 11,
        ]]);

    }
}
