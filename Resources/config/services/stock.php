<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Service\Stock;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Analysis exporter
    $services
        ->set('ekyna_product.stock.analysis_exporter', Stock\Analysis\Exporter::class)
        ->args([
            service('ekyna_product.stock.repository'),
            service('doctrine.dbal.default_connection'),
        ]);

    // Analysis importer
    $services
        ->set('ekyna_product.stock.analysis_importer', Stock\Analysis\Importer::class)
        ->args([
            service('doctrine.dbal.default_connection'),
            service('doctrine.orm.default_entity_manager'),
            param('ekyna_product.class.product'),
        ]);

    // Bundle stock adjuster
    $services
        ->set('ekyna_product.bundle_stock_adjuster', Stock\BundleStockAdjuster::class) // TODO Rename
        ->args([
            service('ekyna_commerce.helper.adjust'),
        ]);

    // Resupply
    $services
        ->set('ekyna_product.resupply', Stock\Resupply::class) // TODO Rename
        ->args([
            service('ekyna_resource.factory.factory'),
            service('ekyna_resource.repository.factory'),
            service('ekyna_resource.manager.factory'),
            service('ekyna_commerce.helper.subject'),
        ]);

    // StockRepository
    $services
        ->set('ekyna_product.stock.repository', Stock\StockRepository::class)
        ->args([
            service('doctrine.orm.default_entity_manager'),
            service('ekyna_commerce.factory.formatter'),
            param('ekyna_product.class.product'),
            param('ekyna_product.class.product_stock_unit'),
            param('ekyna_commerce.class.supplier_order_item'),
            param('ekyna_commerce.class.supplier_product'),
            param('ekyna_commerce.class.production_order'),
            param('ekyna_commerce.class.bill_of_materials'),
        ]);

    // StockView
    $services
        ->set('ekyna_product.stock_view', Stock\StockView::class) // TODO Rename
        ->args([
            service('ekyna_product.stock.repository'),
            service('ekyna_resource.helper'),
            service('router'),
            service('translator'),
            service('form.factory'),
            service('request_stack'),
            service('ekyna_admin.provider.user'),
            service('ekyna_commerce.factory.formatter'),
        ]);
};
