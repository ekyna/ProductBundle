<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryGenerator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Inventory generator
    $services
        ->set('ekyna_product.inventory.generator', InventoryGenerator::class)
        ->args([
            service('ekyna_product.repository.product'),
            service('ekyna_product.repository.product_stock_unit'),
            service('doctrine.orm.default_entity_manager'),
        ]);
};
