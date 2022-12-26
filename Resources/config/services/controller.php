<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Controller\Account\CatalogController;
use Ekyna\Bundle\ProductBundle\Controller\Account\ProductController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\HighlightController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp;
use Ekyna\Bundle\ProductBundle\Controller\Admin\ProductBookmarkController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services
        ->set('ekyna_product.controller.account.product', ProductController::class)
        ->args([
            service('security.authorization_checker'),
            service('ekyna_resource.search'),
        ])
        ->alias(ProductController::class, 'ekyna_product.controller.account.product')
        ->public();

    // Removed by DI extension if catalog feature (and account) is disabled
    $services
        ->set('ekyna_product.controller.account.catalog', CatalogController::class)
        ->args([
            service('ekyna_product.repository.catalog'),
            service('ekyna_product.factory.catalog'),
            service('ekyna_product.manager.catalog'),
            service('ekyna_commerce.provider.context'),
            service('ekyna_product.renderer.catalog'),
            service('ekyna_ui.helper.flash'),
            service('form.factory'),
            service('router'),
            service('twig'),
        ])
        ->call('setCustomerProvider', [service('ekyna_commerce.provider.customer')])
        ->alias(CatalogController::class, 'ekyna_product.controller.account.catalog')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.product_bookmark', ProductBookmarkController::class)
        ->args([
            service('ekyna_admin.provider.user'),
            service('ekyna_product.repository.product'),
            service('ekyna_product.repository.product_bookmark'),
            service('doctrine'),
        ])
        ->alias(ProductBookmarkController::class, 'ekyna_product.controller.admin.product_bookmark')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.highlight', HighlightController::class)
        ->args([
            service('ekyna_product.repository.product'),
            service('ekyna_product.manager.product'),
            service('ekyna_admin.menu.builder'),
            service('translator'),
            service('validator'),
            service('twig'),
        ])
        ->alias(HighlightController::class, 'ekyna_product.controller.admin.highlight')
        ->public();

    // ---------------------------- Inventory ----------------------------

    $services
        ->set(InventoryApp\IndexController::class)
        ->public()
        ->args([
            service('ekyna_product.repository.inventory'),
            service('twig'),
        ]);

    $services
        ->set(InventoryApp\ListController::class)
        ->public()
        ->args([
            service('ekyna_product.repository.inventory'),
            service('ekyna_product.repository.inventory_product'),
            service('serializer'),
        ]);

    $services
        ->set(InventoryApp\CountController::class)
        ->public()
        ->args([
            service('ekyna_product.repository.inventory_product'),
            service('doctrine.orm.default_entity_manager'),
            service('serializer'),
            service('router'),
            service('form.factory'),
            service('ekyna_ui.modal.renderer'),
        ]);

    $services
        ->set(InventoryApp\EndOfLifeController::class)
        ->public()
        ->args([
            service('ekyna_product.repository.inventory_product'),
            service('doctrine.orm.default_entity_manager'),
            service('serializer'),
        ]);

    $services
        ->set(InventoryApp\ValidateController::class)
        ->public()
        ->args([
            service('ekyna_product.repository.inventory_product'),
            service('doctrine.orm.default_entity_manager'),
            service('serializer'),
        ]);

    // ---------------------------- Stock View ----------------------------
    $services
        ->set('ekyna_product.controller.admin.stock_view.index', StockView\IndexController::class)
        ->args([
            service('ekyna_product.stock_view'),
            service('router'),
            service('twig'),
            service('ekyna_admin.menu.builder'),
        ])
        ->alias(StockView\IndexController::class, 'ekyna_product.controller.admin.stock_view.index')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.list', StockView\ListController::class)
        ->args([
            service('ekyna_product.stock_view'),
            service('router'),
            service('twig'),
            service('ekyna_admin.menu.builder'),
        ])
        ->alias(StockView\ListController::class, 'ekyna_product.controller.admin.stock_view.list')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.abstract', StockView\AbstractController::class)
        ->abstract()
        ->call('init', [
            service('ekyna_product.repository.product'),
            service('ekyna_ui.modal.renderer'),
            param('kernel.debug'),
        ]);

    $services
        ->set('ekyna_product.controller.admin.stock_view.quick_edit', StockView\QuickEditController::class)
        ->parent('ekyna_product.controller.admin.stock_view.abstract')
        ->call('setStockView', [service('ekyna_product.stock_view')])
        ->args([
            service('ekyna_product.manager.product'),
            service('form.factory'),
            service('router'),
            service('translator'),
        ])
        ->alias(StockView\QuickEditController::class, 'ekyna_product.controller.admin.stock_view.quick_edit')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.batch_edit', StockView\BatchEditController::class)
        ->parent('ekyna_product.controller.admin.stock_view.abstract')
        ->call('setStockView', [service('ekyna_product.stock_view')])
        ->args([
            service('ekyna_product.manager.product'),
            service('form.factory'),
            service('router'),
            service('validator'),
        ])
        ->alias(StockView\BatchEditController::class, 'ekyna_product.controller.admin.stock_view.batch_edit')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.resupply', StockView\ResupplyController::class)
        ->parent('ekyna_product.controller.admin.stock_view.abstract')
        ->call('setStockView', [service('ekyna_product.stock_view')])
        ->args([
            service('ekyna_commerce.repository.supplier_product'),
            service('ekyna_commerce.repository.supplier_order'),
            service('ekyna_product.resupply'),
            service('form.factory'),
            service('router'),
            service('translator'),
        ])
        ->alias(StockView\ResupplyController::class, 'ekyna_product.controller.admin.stock_view.resupply')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.stock_units', StockView\StockUnitsController::class)
        ->parent('ekyna_product.controller.admin.stock_view.abstract')
        ->args([
            service('ekyna_commerce.renderer.stock'),
            service('translator'),
        ])
        ->alias(StockView\StockUnitsController::class, 'ekyna_product.controller.admin.stock_view.stock_units')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.customer_orders', StockView\CustomerOrdersController::class)
        ->parent('ekyna_product.controller.admin.stock_view.abstract')
        ->args([
            service('ekyna_admin.helper.resource_table'),
            service('translator'),
        ])
        ->alias(StockView\CustomerOrdersController::class, 'ekyna_product.controller.admin.stock_view.customer_orders')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.export', StockView\ExportController::class)
        ->args([
            service('ekyna_product.stock_view'),
        ])
        ->alias(StockView\ExportController::class, 'ekyna_product.controller.admin.stock_view.export')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.export_units', StockView\ExportUnitsController::class)
        ->args([
            service('ekyna_product.repository.product_stock_unit'),
            param('ekyna_commerce.default.currency'),
        ])
        ->alias(StockView\ExportUnitsController::class, 'ekyna_product.controller.admin.stock_view.export_units')
        ->public();

    $services
        ->set('ekyna_product.controller.admin.stock_view.export_products', StockView\ExportProductsController::class)
        ->args([
            service('ekyna_product.repository.product'),
        ])
        ->alias(StockView\ExportProductsController::class, 'ekyna_product.controller.admin.stock_view.export_products')
        ->public();
};
