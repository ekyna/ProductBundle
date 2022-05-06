<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Command\MigratePricingNameCommand;
use Ekyna\Bundle\ProductBundle\Service\Migration\PricingNameMigrator;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Pricing name migrator
        ->set('ekyna_product.migrator.pricing_name', PricingNameMigrator::class)
            ->args([
                service('ekyna_product.generator.pricing_name'),
                service('ekyna_product.repository.pricing'),
                service('ekyna_product.repository.special_offer'),
                service('database_connection'),
            ])

        // Min price update command
        ->set('ekyna_product.command.migrate_pricing_name', MigratePricingNameCommand::class)
            ->args([
                service('ekyna_product.migrator.pricing_name'),
            ])
            ->tag('console.command')
    ;
};
