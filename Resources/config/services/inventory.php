<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryApplier;
use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryCalculator;
use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryGenerator;
use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryHelper;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Inventory applier
    $services
        ->set('ekyna_product.inventory.applier', InventoryApplier::class)
        ->args([
            service('ekyna_product.repository.inventory_product'),
            service('ekyna_product.inventory.calculator'),
            service('ekyna_commerce.helper.adjust'),
            service('doctrine.orm.default_entity_manager'),
        ]);

    // Inventory calculator
    $services
        ->set('ekyna_product.inventory.calculator', InventoryCalculator::class);

    // Inventory generator
    $services
        ->set('ekyna_product.inventory.generator', InventoryGenerator::class)
        ->args([
            service('ekyna_product.repository.product'),
            service('ekyna_product.repository.product_stock_unit'),
            service('doctrine.orm.default_entity_manager'),
        ]);

    // Inventory helper
    $services
        ->set('ekyna_product.inventory.helper', InventoryHelper::class)
        ->args([
            service('ekyna_product.repository.inventory'),
        ])
        ->tag('twig.runtime');
};
