<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductFilter;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Bundle\ProductBundle\Service\Commerce\Report\ProductsSection;
use Ekyna\Bundle\ProductBundle\Service\Commerce\SaleViewType;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\RegisterViewTypePass;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\ReportRegistryPass;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\SubjectProviderPass;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Product subject provider
    $services
        ->set('ekyna_product.commerce.provider.subject', ProductProvider::class)
        ->args([
            service('ekyna_product.repository.product'),
        ])
        ->tag(SubjectProviderPass::TAG);

    // Product filter
    $services
        ->set('ekyna_product.commerce.filter.product', ProductFilter::class);

    // Sale item builder
    $services
        ->set('ekyna_product.commerce.builder.item', ItemBuilder::class)
        ->args([
            service('ekyna_product.commerce.provider.subject'),
            service('ekyna_product.commerce.filter.product'),
        ]);

    // Sale form builder
    $services
        ->set('ekyna_product.commerce.builder.form', FormBuilder::class)
        ->args([
            service('ekyna_product.commerce.provider.subject'),
            service('ekyna_product.commerce.filter.product'),
            service('ekyna_product.calculator.price'),
            service('ekyna_commerce.helper.availability'),
            service('ekyna_resource.provider.locale'),
            service('translator'),
            param('ekyna_product.default.no_image'), // TODO abstract_arg
        ])
        ->call('setCacheManager', [service('liip_imagine.cache.manager')]);

    // Report products section
    $services
        ->set('ekyna_product.commerce.report.section.products', ProductsSection::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.helper.stock_subject_quantity'),
            service('ekyna_commerce.factory.margin_calculator'),
        ])
        ->tag(ReportRegistryPass::SECTION_TAG);

    // Sale view type
    $services
        ->set('ekyna_product.commerce.view_type.sale', SaleViewType::class)
        ->parent('ekyna_commerce.view_type.abstract')
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);
};
