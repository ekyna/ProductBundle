<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Controller\Account\CatalogController;
use Ekyna\Bundle\ProductBundle\Controller\Account\ProductController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\HighlightController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\AbstractController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\BatchEditController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\CustomerOrdersController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\ExportProductsController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\ExportUnitsController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\QuickEditController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\ResupplyController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory\StockUnitsController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryController;
use Ekyna\Bundle\ProductBundle\Controller\Admin\ProductBookmarkController;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->set('ekyna_product.controller.account.product', ProductController::class)
            ->args([
                service('security.authorization_checker'),
                service('ekyna_resource.search'),
            ])
            ->alias(ProductController::class, 'ekyna_product.controller.account.product')->public()

        // Removed by DI extension if catalog feature (and account) is disabled
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
            ->alias(CatalogController::class, 'ekyna_product.controller.account.catalog')->public()

        ->set('ekyna_product.controller.admin.product_bookmark', ProductBookmarkController::class)
            ->args([
                service('ekyna_admin.provider.user'),
                service('ekyna_product.repository.product'),
                service('ekyna_product.repository.product_bookmark'),
                service('doctrine'),
            ])
            ->alias(ProductBookmarkController::class, 'ekyna_product.controller.admin.product_bookmark')->public()

        ->set('ekyna_product.controller.admin.highlight', HighlightController::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('ekyna_product.manager.product'),
                service('ekyna_admin.menu.builder'),
                service('translator'),
                service('validator'),
                service('twig'),
            ])
            ->alias(HighlightController::class, 'ekyna_product.controller.admin.highlight')->public()

        ->set('ekyna_product.controller.admin.inventory', InventoryController::class)
            ->args([
                service('ekyna_product.inventory'),
                service('router'),
                service('twig'),
                service('ekyna_admin.menu.builder'),
            ])
            ->alias(InventoryController::class, 'ekyna_product.controller.admin.inventory')->public()

        ->set('ekyna_product.controller.admin.inventory.abstract', AbstractController::class)
            ->abstract()
            ->call('init', [
                service('ekyna_product.repository.product'),
                service('ekyna_ui.modal.renderer'),
                param('kernel.debug'),
            ])

        ->set('ekyna_product.controller.admin.inventory.quick_edit', QuickEditController::class)
            ->parent('ekyna_product.controller.admin.inventory.abstract')
            ->call('setInventory', [service('ekyna_product.inventory')])
            ->args([
                service('ekyna_product.manager.product'),
                service('form.factory'),
                service('router'),
                service('translator'),
            ])
            ->alias(QuickEditController::class, 'ekyna_product.controller.admin.inventory.quick_edit')->public()

        ->set('ekyna_product.controller.admin.inventory.batch_edit', BatchEditController::class)
            ->parent('ekyna_product.controller.admin.inventory.abstract')
            ->call('setInventory', [service('ekyna_product.inventory')])
            ->args([
                service('ekyna_product.manager.product'),
                service('form.factory'),
                service('router'),
                service('validator'),
            ])
            ->alias(BatchEditController::class, 'ekyna_product.controller.admin.inventory.batch_edit')->public()

        ->set('ekyna_product.controller.admin.inventory.resupply', ResupplyController::class)
            ->parent('ekyna_product.controller.admin.inventory.abstract')
            ->call('setInventory', [service('ekyna_product.inventory')])
            ->args([
                service('ekyna_commerce.repository.supplier_product'),
                service('ekyna_commerce.repository.supplier_order'),
                service('ekyna_product.resupply'),
                service('form.factory'),
                service('router'),
                service('translator'),
            ])
            ->alias(ResupplyController::class, 'ekyna_product.controller.admin.inventory.resupply')->public()

        ->set('ekyna_product.controller.admin.inventory.stock_units', StockUnitsController::class)
            ->parent('ekyna_product.controller.admin.inventory.abstract')
            ->args([
                service('ekyna_commerce.renderer.stock'),
                service('translator'),
            ])
            ->alias(StockUnitsController::class, 'ekyna_product.controller.admin.inventory.stock_units')->public()

        ->set('ekyna_product.controller.admin.inventory.customer_orders', CustomerOrdersController::class)
            ->parent('ekyna_product.controller.admin.inventory.abstract')
            ->args([
                service('ekyna_admin.helper.resource_table'),
                service('translator'),
            ])
            ->alias(CustomerOrdersController::class, 'ekyna_product.controller.admin.inventory.customer_orders')->public()

        ->set('ekyna_product.controller.admin.inventory.export_units', ExportUnitsController::class)
            ->args([
                service('ekyna_product.repository.product_stock_unit'),
                param('ekyna_commerce.default.currency'),
            ])
            ->alias(ExportUnitsController::class, 'ekyna_product.controller.admin.inventory.export_units')->public()

        ->set('ekyna_product.controller.admin.inventory.export_products', ExportProductsController::class)
            ->args([
                service('ekyna_product.repository.product'),
            ])
            ->alias(ExportProductsController::class, 'ekyna_product.controller.admin.inventory.export_products')->public()
    ;
};
