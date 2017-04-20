<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\EventListener\AccountDashboardSubscriber;
use Ekyna\Bundle\ProductBundle\EventListener\AccountMenuSubscriber;
use Ekyna\Bundle\ProductBundle\EventListener\AddToCartEventSubscriber;
use Ekyna\Bundle\ProductBundle\EventListener\BarcodeListener;
use Ekyna\Bundle\ProductBundle\EventListener\BundleChoiceListener;
use Ekyna\Bundle\ProductBundle\EventListener\BundleSlotListener;
use Ekyna\Bundle\ProductBundle\EventListener\CategoryListener;
use Ekyna\Bundle\ProductBundle\EventListener\CheckoutEventSubscriber;
use Ekyna\Bundle\ProductBundle\EventListener\ComponentListener;
use Ekyna\Bundle\ProductBundle\EventListener\CustomerGroupListener;
use Ekyna\Bundle\ProductBundle\EventListener\Handler;
use Ekyna\Bundle\ProductBundle\EventListener\LabelListener;
use Ekyna\Bundle\ProductBundle\EventListener\OfferListener;
use Ekyna\Bundle\ProductBundle\EventListener\OptionGroupListener;
use Ekyna\Bundle\ProductBundle\EventListener\OptionListener;
use Ekyna\Bundle\ProductBundle\EventListener\PriceListener;
use Ekyna\Bundle\ProductBundle\EventListener\PricingListener;
use Ekyna\Bundle\ProductBundle\EventListener\PricingRuleListener;
use Ekyna\Bundle\ProductBundle\EventListener\ProductListener;
use Ekyna\Bundle\ProductBundle\EventListener\ProductMediaListener;
use Ekyna\Bundle\ProductBundle\EventListener\ProductStockUnitListener;
use Ekyna\Bundle\ProductBundle\EventListener\ProductTranslationListener;
use Ekyna\Bundle\ProductBundle\EventListener\SaleButtonsEventSubscriber;
use Ekyna\Bundle\ProductBundle\EventListener\SaleItemEventSubscriber;
use Ekyna\Bundle\ProductBundle\EventListener\SpecialOfferListener;
use Ekyna\Bundle\ProductBundle\EventListener\ImageUrlEventListener;
use Symfony\Component\Console\ConsoleEvents;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Account dashboard event listener
        ->set('ekyna_product.listener.account.dashboard', AccountDashboardSubscriber::class)
            ->args([
                service('ekyna_commerce.provider.context'),
                service('ekyna_product.repository.pricing'),
            ])
            ->tag('kernel.event_subscriber')

        // Account menu event listener
        ->set('ekyna_product.listener.account.menu', AccountMenuSubscriber::class)
            ->args([
                service('ekyna_commerce.provider.customer'),
                abstract_arg('Account menu configuration'),
            ])
            ->tag('kernel.event_subscriber')

        // Barcode event listener
        ->set('ekyna_product.listener.barcode', BarcodeListener::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('router'),
            ])
            ->tag('kernel.event_subscriber')

        // Category resource event listener
        ->set('ekyna_product.listener.category', CategoryListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Bundle choice resource event listener
        ->set('ekyna_product.listener.bundle_choice', BundleChoiceListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Bundle choice resource event listener
        ->set('ekyna_product.listener.bundle_slot', BundleSlotListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Component resource event listener
        ->set('ekyna_product.listener.component', ComponentListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Customer group resource event listener
        ->set('ekyna_product.listener.customer_group', CustomerGroupListener::class)
            ->args([
                service('ekyna_product.invalidator.price'),
            ])
            ->tag('resource.event_subscriber')

        // Option resource event listener
        ->set('ekyna_product.listener.option', OptionListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Option group resource event listener
        ->set('ekyna_product.listener.option', OptionGroupListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Price resource event listener
        ->set('ekyna_product.listener.price', PriceListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.repository.customer_group'),
                service('ekyna_commerce.repository.country'),
            ])
            ->tag('resource.event_subscriber')
            ->tag('kernel.event_listener', ['event' => ConsoleEvents::TERMINATE, 'method' => 'onTerminate'])

        // Offer resource event listener
        ->set('ekyna_product.listener.offer', OfferListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_commerce.repository.customer_group'),
                service('ekyna_commerce.repository.country'),
            ])
            ->tag('resource.event_subscriber')
            ->tag('kernel.event_listener', ['event' => ConsoleEvents::TERMINATE, 'method' => 'onTerminate'])

        // Special offer resource event listener
        ->set('ekyna_product.listener.special_offer', SpecialOfferListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_product.invalidator.offer'),
                service('ekyna_product.invalidator.price'),
                service('translator'),
            ])
            ->tag('resource.event_subscriber')

        // Pricing resource event listener
        ->set('ekyna_product.listener.pricing', PricingListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_product.invalidator.offer'),
                service('ekyna_product.invalidator.price'),
                service('translator'),
            ])
            ->tag('resource.event_subscriber')

        // Pricing rule resource event listener
        ->set('ekyna_product.listener.pricing', PricingRuleListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_product.invalidator.offer'),
            ])
            ->tag('resource.event_subscriber')

        // Add to cart event listener
        ->set('ekyna_product.listener.add_to_cart', AddToCartEventSubscriber::class)
            ->args([
                service('twig'),
                abstract_arg('Add to cart template'),
            ])
            ->tag('kernel.event_subscriber')

        // Sale buttons event listener
        ->set('ekyna_product.listener.sale_buttons', SaleButtonsEventSubscriber::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('kernel.event_subscriber')

        // Sale item event listener
        ->set('ekyna_product.listener.sale_item', SaleItemEventSubscriber::class)
            ->args([
                service('ekyna_commerce.provider.context'),
                service('ekyna_product.commerce.builder.item'),
                service('ekyna_product.commerce.builder.form'),
                service('ekyna_product.repository.offer'),
                service('translator'),
            ])
            ->tag('kernel.event_subscriber')

        // Product event handlers registry
        ->set('ekyna_product.registry.event_handler', Handler\HandlerRegistry::class)

        // Simple product event handler
        ->set('ekyna_product.listener.handler.simple', Handler\SimpleHandler::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_product.calculator.price'),
                service('ekyna_commerce.updater.stock_subject'),
                service('ekyna_product.repository.product'),
                service('ekyna_product.invalidator.price'),
            ])
            ->tag(Handler\HandlerInterface::DI_TAG)

        // Variant product event handler
        ->set('ekyna_product.listener.handler.variant', Handler\VariantHandler::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_resource.provider.locale'),
                service('ekyna_product.calculator.price'),
                service('ekyna_product.registry.attribute_type'),
                service('ekyna_product.repository.product'),
            ])
            ->tag(Handler\HandlerInterface::DI_TAG)

        // Variable product event handler
        ->set('ekyna_product.listener.handler.variable', Handler\VariableHandler::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_resource.provider.locale'),
                service('ekyna_product.calculator.price'),
                service('ekyna_product.registry.attribute_type'),
                service('ekyna_product.repository.product'),
            ])
            ->call('setPriceInvalidator', [service('ekyna_product.invalidator.price')])
            ->call('setStockUpdater', [service('ekyna_commerce.updater.stock_subject')])
            ->tag(Handler\HandlerInterface::DI_TAG)

        // Bundle product event handler
        ->set('ekyna_product.listener.handler.bundle', Handler\BundleHandler::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_product.repository.product'),
                service('ekyna_product.calculator.price'),
                service('ekyna_product.invalidator.price'),
                service('ekyna_commerce.updater.stock_subject'),
            ])
            ->tag(Handler\HandlerInterface::DI_TAG)

        // Configurable product event handler
        ->set('ekyna_product.listener.handler.configurable', Handler\ConfigurableHandler::class)
            ->args([
                service('ekyna_product.calculator.price'),
                service('ekyna_product.invalidator.price'),
                service('ekyna_commerce.updater.stock_subject'),
            ])
            ->tag(Handler\HandlerInterface::DI_TAG)

        // Product resource event listener
        ->set('ekyna_product.listener.product', ProductListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
                service('ekyna_product.registry.event_handler'),
                service('ekyna_product.generator.reference'),
                service('ekyna_product.invalidator.offer'),
                service('ekyna_product.invalidator.price'),
                service('ekyna_commerce.updater.stock_subject'),
            ])
            ->tag('resource.event_subscriber')

        // Product media resource event listener
        ->set('ekyna_product.listener.product_media', ProductMediaListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Product stock unit resource event listener
        ->set('ekyna_product.listener.product_stock_unit', ProductStockUnitListener::class)
            ->parent('ekyna_commerce.listener.abstract_stock_unit')
            ->tag('resource.event_subscriber')

        // Product translation resource event listener
        ->set('ekyna_product.listener.product_translation', ProductTranslationListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_helper'),
            ])
            ->tag('resource.event_subscriber')

        // Product label event listener
        ->set('ekyna_product.listener.product_label', LabelListener::class)
            ->tag('kernel.event_subscriber')

        // Image url event listener
        ->set('ekyna_product.listener.image_url', ImageUrlEventListener::class)
            ->tag('resource.event_subscriber')

        // Checkout event listener
        ->set('ekyna_product.listener.checkout', CheckoutEventSubscriber::class)
            ->args([
                service('ekyna_product.highlight'),
            ])
            ->tag('kernel.event_subscriber')
    ;
};
