<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Attribute;
use Ekyna\Bundle\ProductBundle\Attribute\Type\TypeInterface;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Bundle\ProductBundle\Service\Converter;
use Ekyna\Bundle\ProductBundle\Service\Editor\Block\ProductSlidePlugin;
use Ekyna\Bundle\ProductBundle\Service\Exporter\ProductExporter;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Bundle\ProductBundle\Service\Generator;
use Ekyna\Bundle\ProductBundle\Service\Google\TrackingHelper;
use Ekyna\Bundle\ProductBundle\Service\Highlight\Highlight;
use Ekyna\Bundle\ProductBundle\Service\Pricing;
use Ekyna\Bundle\ProductBundle\Service\Routing\RoutingLoader;
use Ekyna\Bundle\ProductBundle\Service\SchemaOrg;
use Ekyna\Bundle\ProductBundle\Service\Stat;
use Ekyna\Bundle\ProductBundle\Service\Stock;
use Ekyna\Component\Resource\Event\QueueEvents;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->set('ekyna_product.features', Features::class)
            ->args([
                abstract_arg('Features configuration'),
            ])
            ->tag('twig.runtime')

        /*->set('ekyna_product.cache')
            ->parent('cache.app')
            ->private()
            ->tag('cache.pool', ['clearer' => 'cache.default_clearer'])*/

        ->set('ekyna_product.loader.routing', RoutingLoader::class)
            ->args([
                abstract_arg('Product routing configuration'), // TODO Inject features
                param('ekyna_user.account_routing_prefix'),
                param('kernel.debug'),
            ])
            ->tag('routing.loader')

        // Attributes
        ->set('ekyna_product.registry.attribute_type', Attribute\AttributeTypeRegistry::class)
        ->set('ekyna_product.renderer.attribute', Attribute\AttributeRenderer::class)
            ->args([
                service('ekyna_product.registry.attribute_type'),
                service('ekyna_resource.provider.locale'),
            ])
            ->tag('twig.runtime')
        ->set('ekyna_product.attribute.type.select', Attribute\Type\SelectType::class)
            ->tag(TypeInterface::TYPE_TAG)
        ->set('ekyna_product.attribute.type.text', Attribute\Type\TextType::class)
            ->tag(TypeInterface::TYPE_TAG)
        ->set('ekyna_product.attribute.type.boolean', Attribute\Type\BooleanType::class)
            ->args([
                service('translator'),
                param('ekyna_resource.locales'),
                param('kernel.default_locale'),
            ])
            ->tag(TypeInterface::TYPE_TAG)
        ->set('ekyna_product.attribute.type.unit', Attribute\Type\UnitType::class)
            ->args([
                service('translator'),
            ])
            ->tag(TypeInterface::TYPE_TAG)

        // StockView
        ->set('ekyna_product.stock_view', Stock\StockView::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_resource.helper'),
                service('router'),
                service('translator'),
                service('form.factory'),
                service('request_stack'),
                service('ekyna_commerce.factory.formatter'),
                service('ekyna_admin.provider.user'),
                param('ekyna_product.class.product'),
                param('ekyna_commerce.class.supplier_product'),
                param('ekyna_commerce.class.supplier_order_item'),
                param('ekyna_product.class.product_stock_unit'),
            ])

        // Resupply
        ->set('ekyna_product.resupply', Stock\Resupply::class)
            ->args([
                service('ekyna_resource.factory.factory'),
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.manager.factory'),
                service('ekyna_commerce.helper.subject'),
            ])

        // Constant helper
        ->set('ekyna_product.helper.constants', ConstantsHelper::class)
            ->args([
                service('translator'),
                service('ekyna_product.registry.attribute_type'),
            ])
            ->tag('twig.runtime')

        // Name generator
        ->set('ekyna_product.generator.pricing_name', Generator\PricingNameGenerator::class)

        // Offer resolver
        ->set('ekyna_product.resolver.offer', Pricing\OfferResolver::class)
            ->args([
                service('ekyna_product.repository.pricing'),
                service('ekyna_product.repository.special_offer'),
                service('ekyna_product.calculator.price'),
            ])

        // Offer invalidator
        ->set('ekyna_product.invalidator.offer', Pricing\OfferInvalidator::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_product.repository.product'),
                service('ekyna_resource.queue.message'),
                param('ekyna_product.class.offer'),
            ])
            ->tag('resource.event_listener', [
                'event'    => QueueEvents::QUEUE_CLOSE,
                'method'   => 'flush',
                'priority' => 1,
            ])

        // Offer updater
        ->set('ekyna_product.updater.offer', Pricing\OfferUpdater::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_product.resolver.offer'),
                service('ekyna_product.repository.offer'),
                service('ekyna_product.invalidator.offer'),
                service('ekyna_product.invalidator.price'),
                param('ekyna_commerce.class.customer_group'),
                param('ekyna_commerce.class.country'),
                param('ekyna_product.class.pricing'),
                param('ekyna_product.class.special_offer'),
            ])

        // Price invalidator
        ->set('ekyna_product.invalidator.price', Pricing\PriceInvalidator::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_product.repository.product'),
                service('ekyna_resource.queue.message'),
                param('ekyna_product.class.price'),
            ])
            ->tag('resource.event_listener', [
                'event'  => QueueEvents::QUEUE_CLOSE,
                'method' => 'flush',
            ])

        // Price updater
        ->set('ekyna_product.updater.price', Pricing\PriceUpdater::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service('ekyna_product.resolver.offer'),
                service('ekyna_product.factory.price'),
                service('ekyna_product.repository.price'),
                service('ekyna_product.invalidator.price'),
                param('ekyna_commerce.class.customer_group'),
                param('ekyna_commerce.class.country'),
            ])

        // Price calculator
        ->set('ekyna_product.calculator.price', Pricing\PriceCalculator::class)
            ->lazy()
            ->args([
                service('ekyna_product.repository.price'),
                service('ekyna_product.repository.offer'),
                service('ekyna_commerce.resolver.tax'),
                service('ekyna_commerce.converter.currency'),
                param('ekyna_commerce.default.currency'),
            ])

        // Purchase cost calculator
        ->set('ekyna_product.calculator.purchase_cost', Pricing\PurchaseCostCalculator::class)
            ->lazy()
            ->args([
                service('ekyna_product.calculator.price'),
                service('ekyna_commerce.guesser.subject_purchase_cost'),
                param('ekyna_commerce.default.currency'),
            ])

        // Pricing renderer cost calculator
        ->set('ekyna_product.renderer.pricing', Pricing\PriceRenderer::class)
            ->lazy()
            ->args([
                service('ekyna_product.calculator.price'),
                service('ekyna_product.calculator.purchase_cost'),
                service('ekyna_commerce.provider.context'),
                service('ekyna_commerce.factory.formatter'),
                service('translator'),
                service('twig'),
                abstract_arg('Pricing renderer configuration'),
            ])
            ->tag('twig.runtime')

        // Variant generator
        ->set('ekyna_product.generator.variant', Generator\VariantGenerator::class)
            ->args([
                service('ekyna_product.factory.product'),
                service('ekyna_resource.copier'),
            ])

        // Reference generator
        ->set('ekyna_product.generator.reference', Generator\ReferenceGenerator::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/product_reference'"),
                8,
                'ym',
                param('kernel.debug'),
            ])

        // External reference generator
        ->set('ekyna_product.generator.external_reference', Generator\ExternalReferenceGenerator::class)
            ->args([
                service('ekyna_product.repository.product_reference'),
                service('ekyna_product.factory.product_reference'),
            ])

        // Product converter
        ->set('ekyna_product.converter.product', Converter\ProductConverter::class)
            ->tag('twig.runtime')

        // Abstract product converter
        ->set('ekyna_product.converter.product.abstract', Converter\AbstractConverter::class)
            ->abstract()
            ->args([
                service('ekyna_product.factory.product'),
                service('ekyna_product.manager.product'),
                service('doctrine.orm.default_entity_manager'),
                service('form.factory'),
                service('request_stack'),
                service('validator'),
                service('ekyna_resource.event_dispatcher'),
                service('ekyna_product.invalidator.offer'),
            ])

        // Simple to variable product converter
        ->set('ekyna_product.converter.product.simple_to_variable', Converter\SimpleToVariableConverter::class)
            ->parent('ekyna_product.converter.product.abstract')
            ->tag(Converter\ConverterInterface::DI_TAG)

        // Bundle to simple product converter
        ->set('ekyna_product.converter.product.bundle_to_simple', Converter\BundleToSimpleConverter::class)
            ->parent('ekyna_product.converter.product.abstract')
            ->call('setStockSubjectUpdater', [service('ekyna_commerce.updater.stock_subject')])
            ->call('setCopier', [service('ekyna_resource.copier')])
            ->tag(Converter\ConverterInterface::DI_TAG)

        // CMS Editor plugins
        ->set('ekyna_product.editor.block_plugin.product_slide', ProductSlidePlugin::class)
            ->parent('ekyna_cms.editor.block_plugin.abstract')
            ->args([
                service('ekyna_product.repository.product'),
                service('twig'),
                param('ekyna_product.editor.slide'), // TODO abstract_arg
            ])
            ->tag('ekyna_cms.editor.block_plugin')

        // Product exporter
        ->set('ekyna_product.exporter.product', ProductExporter::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('ekyna_product.calculator.price'),
                service('ekyna_product.calculator.purchase_cost'),
                service('ekyna_commerce.helper.subject'),
                service('translator'),
            ])

        // Catalog registry
        ->set('ekyna_product.registry.catalog', CatalogRegistry::class)
            ->args([
                abstract_arg('Catalog themes configuration'),
                abstract_arg('Catalog templates configuration'),
            ])

        // Catalog renderer
        ->set('ekyna_product.renderer.catalog', CatalogRenderer::class)
            ->args([
                service('ekyna_product.registry.catalog'),
                service('twig'),
                service('ekyna_resource.generator.pdf'),
                service('ekyna_commerce.helper.subject'),
                param('ekyna_commerce.default.company_logo'),
                param('kernel.debug'),
            ])

        // Stat updater
        ->set('ekyna_product.updater.stat', Stat\StatUpdater::class)
            ->args([
                service('ekyna_product.repository.stat_count'),
                service('ekyna_product.repository.stat_cross'),
                service('ekyna_product.repository.product'),
                service('ekyna_commerce.repository.customer_group'),
                service('doctrine.orm.default_entity_manager'),
            ])

        // Stat chart builder factory
        ->set('ekyna_product.factory.stat_chart_builder', Stat\ChartBuilderFactory::class)
            ->args([
                service('ekyna_product.repository.stat_count'),
                service('ekyna_product.repository.stat_cross'),
                service('ekyna_product.repository.product'),
                service('ekyna_commerce.repository.customer_group'),
            ])

        // Stat chart renderer
        ->set('ekyna_product.renderer.stat_chart', Stat\ChartRenderer::class)
            ->args([
                service('ekyna_product.factory.stat_chart_builder'),
                service('twig'),
                [], //abstract_arg('Stat chart renderer configuration'),
            ])
            ->tag('twig.runtime')

        // Highlight
        ->set('ekyna_product.highlight', Highlight::class)
            ->args([
                service('ekyna_commerce.provider.context'),
                service('ekyna_commerce.provider.cart'),
                service('ekyna_product.repository.product'),
                service('ekyna_product.repository.stat_count'),
                service('ekyna_product.repository.stat_cross'),
                service('twig'),
                abstract_arg('Highlight configuration'),
            ])
            ->tag('twig.runtime')

        // Google tracking helper
        ->set('ekyna_product.helper.google_tracking', TrackingHelper::class)
            ->args([
                service('ekyna_google.tracking.pool'),
            ])
            ->alias(TrackingHelper::class, 'ekyna_product.helper.google_tracking')

        // Product schema.org provider
        ->set('ekyna_product.schema_org.product', SchemaOrg\ProductProvider::class)
            ->args([
                service('ekyna_resource.event_dispatcher'),
                param('ekyna_commerce.default.currency'),
            ])
            ->tag('ekyna_cms.schema_org_provider')

        // Brand schema.org provider
        ->set('ekyna_product.schema_org.brand', SchemaOrg\BrandProvider::class)
            ->tag('ekyna_cms.schema_org_provider')
    ;
};
