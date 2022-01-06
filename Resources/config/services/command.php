<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Command\OfferInvalidateCommand;
use Ekyna\Bundle\ProductBundle\Command\OfferUpdateCommand;
use Ekyna\Bundle\ProductBundle\Command\ReferenceConvertCommand;
use Ekyna\Bundle\ProductBundle\Command\ResupplyCommand;
use Ekyna\Bundle\ProductBundle\Command\StatUpdateCommand;
use Ekyna\Bundle\ProductBundle\Command\StockReportCommand;
use Ekyna\Bundle\ProductBundle\Command\StockShowCommand;
use Ekyna\Bundle\ProductBundle\Command\StockUpdateCommand;
use Ekyna\Bundle\ProductBundle\Command\UpdateMinPriceCommand;
use Ekyna\Bundle\ProductBundle\Command\VariableFixVisibilityCommand;
use Ekyna\Bundle\ProductBundle\Command\VariantFixPositionCommand;
use Ekyna\Bundle\ProductBundle\Command\WeightFromSupplierCommand;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Offer invalidate command
        ->set('ekyna_product.command.offer_invalidate', OfferInvalidateCommand::class)
            ->args([
                service('ekyna_product.repository.special_offer'),
                service('ekyna_product.invalidator.offer'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('console.command')

        // Offer update command
        ->set('ekyna_product.command.offer_update', OfferUpdateCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('ekyna_product.updater.offer'),
                service('ekyna_product.updater.price'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('console.command')

        // Resupply command
        ->set('ekyna_product.command.resupply', ResupplyCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('ekyna_commerce.repository.supplier_product'),
                service('ekyna_commerce.manager.supplier'),
                service('ekyna_commerce.factory.supplier_delivery'),
                service('ekyna_commerce.factory.supplier_delivery_item'),
                service('ekyna_commerce.manager.supplier_delivery'),
                service('ekyna_product.resupply'),
            ])
            ->tag('console.command')

        // Stat update command
        ->set('ekyna_product.command.stat_update', StatUpdateCommand::class)
            ->args([
                service('ekyna_product.updater.stat'),
            ])
            ->tag('console.command')

        // Stock report command
        ->set('ekyna_product.command.stock_report', StockReportCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('twig'),
                service('ekyna_setting.manager'),
                service('translator'),
                service('mailer'),
            ])
            ->tag('console.command')

        // Stock show command
        ->set('ekyna_product.command.stock_show', StockShowCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
            ])
            ->tag('console.command')

        // Stock update command
        ->set('ekyna_product.command.stock_update', StockUpdateCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('ekyna_commerce.updater.stock_subject'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('console.command')

        // Min price update command
        ->set('ekyna_product.command.min_price_update', UpdateMinPriceCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_product.calculator.price'),
            ])
            ->tag('console.command')

        // Variable fix visibility command
        ->set('ekyna_product.command.variable_fix_visibility', VariableFixVisibilityCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_product.calculator.price'),
            ])
            ->tag('console.command')

        // Variable fix position command
        ->set('ekyna_product.command.variant_fix_position', VariantFixPositionCommand::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_product.calculator.price'),
            ])
            ->tag('console.command')

        // Variable fix position command
        ->set('ekyna_product.command.weight_from_supplier', WeightFromSupplierCommand::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                param('ekyna_product.class.product'),
                param('ekyna_commerce.class.supplier_product'),
            ])
            ->tag('console.command')
    ;
};