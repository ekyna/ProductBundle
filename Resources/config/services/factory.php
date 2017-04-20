<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Factory\ProductFactory;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Product factory
        ->set('ekyna_product.factory.product', ProductFactory::class)
            ->args([
                service('ekyna_commerce.updater.stock_subject'),
            ])
    ;
};
