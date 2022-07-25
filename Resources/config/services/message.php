<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\MessageHandler\UpdateOffersHandler;
use Ekyna\Bundle\ProductBundle\MessageHandler\UpdatePricesHandler;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // UpdateOffers message handler
        ->set('ekyna_product.message_handler.update_offers', UpdateOffersHandler::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('ekyna_product.updater.offer'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('messenger.message_handler')

        // UpdatePrices message handler
        ->set('ekyna_product.message_handler.update_prices', UpdatePricesHandler::class)
            ->args([
                service('ekyna_product.repository.product'),
                service('ekyna_product.updater.price'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('messenger.message_handler')
    ;
};
