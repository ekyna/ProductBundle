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

        $pool->addMethodCall('createGroupReference', array(
            'prod', 'Catalogue', 'folder-open', null, 10
        ));
        $pool->addMethodCall('createEntryReference', array(
            'prod', 'options', 'ekyna_product_optionGroup_admin_home', 'ekyna_product.optionGroup.label.plural'
        ));
        $pool->addMethodCall('createEntryReference', array(
            'prod', 'taxs', 'ekyna_product_tax_admin_home', 'ekyna_product.tax.label.plural'
        ));
    }
}
