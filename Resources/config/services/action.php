<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Action\Admin\Attribute;
use Ekyna\Bundle\ProductBundle\Action\Admin\Catalog;
use Ekyna\Bundle\ProductBundle\Action\Admin\Inventory;
use Ekyna\Bundle\ProductBundle\Action\Admin\Product;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Attribute actions
    $services
        ->set('ekyna_product.action.admin.attribute.create', Attribute\CreateAction::class)
        ->args([
            service('ekyna_product.registry.attribute_type'),
        ])
        ->tag('ekyna_resource.action');

    // Catalog actions
    $services
        ->set('ekyna_product.action.admin.catalog.page_form', Catalog\PageFormAction::class)
        ->args([
            service('ekyna_product.registry.catalog'),
            service('form.factory'),
            service('twig'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.catalog.abstract_render', Catalog\AbstractRenderAction::class)
        ->abstract()
        ->args([
            service('ekyna_commerce.provider.context'),
            service('ekyna_product.renderer.catalog'),
            service('ekyna_commerce.helper.factory'),
        ]);

    $services
        ->set('ekyna_product.action.admin.catalog.render', Catalog\RenderAction::class)
        ->parent('ekyna_product.action.admin.catalog.abstract_render')
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.catalog.render_from_sale', Catalog\RenderFromSaleAction::class)
        ->parent('ekyna_product.action.admin.catalog.abstract_render')
        ->tag('ekyna_resource.action');

    // Inventory actions
    $services
        ->set('ekyna_product.action.admin.inventory.create', Inventory\CreateAction::class)
        ->args([
            service('ekyna_product.repository.inventory'),
            service('ekyna_product.inventory.generator'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.inventory.read', Inventory\ReadAction::class)
        ->args([
            service('ekyna_product.repository.inventory_product'),
        ])
        ->tag('ekyna_resource.action');

    // Product actions
    $services
        ->set('ekyna_product.action.admin.product.adjust_stock', Product\AdjustStockAction::class)
        ->args([
            service('ekyna_product.bundle_stock_adjuster'),
            service('doctrine.orm.default_entity_manager'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.attributes_form', Product\AttributesFormAction::class)
        ->args([
            service('ekyna_product.builder.product_form'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.convert', Product\ConvertAction::class)
        ->args([
            service('ekyna_product.converter.product'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.create', Product\CreateAction::class)
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.duplicate', Product\DuplicateAction::class)
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.export', Product\ExportAction::class)
        ->args([
            service('ekyna_commerce.provider.context'),
            service('ekyna_product.exporter.product'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.generate_reference', Product\GenerateReferenceAction::class)
        ->args([
            service('ekyna_product.generator.external_reference'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.invalidate_offers', Product\InvalidateOffersAction::class)
        ->args([
            service('ekyna_product.invalidator.offer'),
        ])
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.move_up', Product\MoveUpAction::class)
        ->tag('ekyna_resource.action');

    $services
        ->set('ekyna_product.action.admin.product.move_down', Product\MoveDownAction::class)
        ->tag('ekyna_resource.action');
};
