<?php

namespace Ekyna\Bundle\ProductBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * AdminMenuPass
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $pool = $container->getDefinition('ekyna_admin.menu.pool');
        $pool->addMethodCall('createGroup', array(array(
            'name'     => 'catalog',
            'label'    => 'Catalogue',
            'icon'     => 'folder-open',
            'position' => 10,
        )));
        $pool->addMethodCall('createEntry', array('catalog', array(
            'name'     => 'categories',
            'route'    => 'ekyna_product_category_admin_home',
            'label'    => 'ekyna_product.category.label.plural',
            'resource' => 'ekyna_product_category',
            'position' => 98,
        )));
        $pool->addMethodCall('createEntry', array('catalog', array(
            'name'     => 'taxes',
            'route'    => 'ekyna_product_tax_admin_home',
            'label'    => 'ekyna_product.tax.label.plural',
            'resource' => 'ekyna_product_tax',
            'position' => 99,
        )));
    }
}
