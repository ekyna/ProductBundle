<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Action\Admin\Attribute\CreateAction;
use Ekyna\Bundle\ProductBundle\Action\Admin\Catalog;
use Ekyna\Bundle\ProductBundle\Action\Admin\Product;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Attribute actions
        ->set('ekyna_product.action.admin.attribute.create', CreateAction::class)
            ->args([
                service('ekyna_product.registry.attribute_type'),
            ])
            ->tag('ekyna_resource.action')

        // Catalog actions
        ->set('ekyna_product.action.admin.catalog.page_form', Catalog\PageFormAction::class)
            ->args([
                service('ekyna_product.registry.catalog'),
                service('form.factory'),
                service('twig'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.catalog.abstract_render', Catalog\AbstractRenderAction::class)
            ->abstract(true)
            ->args([
                service('ekyna_commerce.provider.context'),
                service('ekyna_product.renderer.catalog'),
                service('ekyna_commerce.factory.sale'),
            ])

        ->set('ekyna_product.action.admin.catalog.render', Catalog\RenderAction::class)
            ->parent('ekyna_product.action.admin.catalog.abstract_render')
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.catalog.render_from_sale', Catalog\RenderFromSaleAction::class)
            ->parent('ekyna_product.action.admin.catalog.abstract_render')
            ->tag('ekyna_resource.action')

        // Product actions
        ->set('ekyna_product.action.admin.product.attributes_form', Product\AttributesFormAction::class)
            ->args([
                service('ekyna_product.builder.product_form'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.convert', Product\ConvertAction::class)
            ->args([
                service('ekyna_product.converter.product'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.create', Product\CreateAction::class)
            ->args([
                // TODO service('twig'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.duplicate', Product\DuplicateAction::class)
            ->args([
                // TODO service('twig'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.export', Product\ExportAction::class)
            ->args([
                service('ekyna_commerce.provider.context'),
                service('ekyna_product.exporter.product'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.generate_reference', Product\GenerateReferenceAction::class)
            ->args([
                service('ekyna_product.generator.external_reference'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.invalidate_offers', Product\InvalidateOffersAction::class)
            ->args([
                // TODO service('twig'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.move_up', Product\MoveUpAction::class)
            ->args([
                // TODO service('twig'),
            ])
            ->tag('ekyna_resource.action')

        ->set('ekyna_product.action.admin.product.move_down', Product\MoveDownAction::class)
            ->args([
                // TODO service('twig'),
            ])
            ->tag('ekyna_resource.action')
    ;
};
