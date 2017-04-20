<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Repository\OfferRepository;
use Ekyna\Bundle\ProductBundle\Repository\PriceRepository;
use Ekyna\Bundle\ProductBundle\Repository\ProductBookmarkRepository;
use Ekyna\Bundle\ProductBundle\Repository\ProductTranslationRepository;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
use Ekyna\Bundle\ProductBundle\Repository\StatCrossRepository;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Product bookmark repository
        ->set('ekyna_product.repository.product_bookmark', ProductBookmarkRepository::class)
            ->args([
                service('doctrine'),
            ])
            ->tag('doctrine.repository_service')

        // Product translation repository
        ->set('ekyna_product.repository.product_translation', ProductTranslationRepository::class)
            ->args([
                service('doctrine'),
                param('ekyna_product.class.product_translation'),
            ])
            ->tag('doctrine.repository_service')

        // Offer repository
        ->set('ekyna_product.repository.offer', OfferRepository::class)
            ->call('setCachedCountryCodes', [param('ekyna_commerce.cache.countries')])

        // Price repository
        ->set('ekyna_product.repository.price', PriceRepository::class)
            ->call('setCachedCountryCodes', [param('ekyna_commerce.cache.countries')])

        // Stat count repository
        ->set('ekyna_product.repository.stat_count', StatCountRepository::class)
            ->args([
                service('doctrine'),
            ])
            ->call('setLocaleProvider', [service('ekyna_resource.provider.locale')])
            ->tag('doctrine.repository_service')

        // Stat cross repository
        ->set('ekyna_product.repository.stat_cross', StatCrossRepository::class)
            ->args([
                service('doctrine'),
            ])
            ->call('setLocaleProvider', [service('ekyna_resource.provider.locale')])
            ->tag('doctrine.repository_service')
    ;
};
