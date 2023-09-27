<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\DataFixtures\FixturesListener;
use Ekyna\Bundle\ProductBundle\DataFixtures\ORM\ProductProvider;
use Ekyna\Bundle\ProductBundle\DataFixtures\ProductProcessor;
use Ekyna\Bundle\ResourceBundle\DataFixtures\Event\FixturesLoadingEnd;
use Ekyna\Bundle\ResourceBundle\DataFixtures\Event\FixturesLoadingStart;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Product fixtures processor
    $services
        ->set('ekyna_product.processor.data_fixtures', ProductProcessor::class)
        ->args([
            service('ekyna_product.generator.external_reference'),
            service('ekyna_media.repository.media'),
        ])
        ->tag('fidry_alice_data_fixtures.processor');

    // Product fixtures provider
    $services
        ->set('ekyna_product.provider.data_fixtures', ProductProvider::class)
        ->args([
            service('ekyna_product.repository.product'),
        ])
        ->tag('nelmio_alice.faker.provider');

    // Product fixtures provider
    $services
        ->set('ekyna_product.listener.data_fixtures', FixturesListener::class)
        ->args([
            service('ekyna_product.invalidator.offer'),
            service('ekyna_product.invalidator.price'),
        ])
        ->tag('kernel.event_listener', [
            'event'  => FixturesLoadingStart::class,
            'method' => 'onStart',
        ])
        ->tag('kernel.event_listener', [
            'event'  => FixturesLoadingEnd::class,
            'method' => 'onEnd',
        ]);
};
