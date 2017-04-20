<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Twig\CatalogExtension;
use Ekyna\Bundle\ProductBundle\Twig\CatalogHelper;
use Ekyna\Bundle\ProductBundle\Twig\HighlightExtension;
use Ekyna\Bundle\ProductBundle\Twig\ProductExtension;
use Ekyna\Bundle\ProductBundle\Twig\ProductHelper;
use Ekyna\Bundle\ProductBundle\Twig\ProductReadHelper;
use Ekyna\Bundle\ProductBundle\Twig\StatExtension;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Product helper
        ->set('ekyna_product.twig.helper.product', ProductHelper::class)
            ->args([
                service('ekyna_product.repository.product'),
                param('ekyna_product.default.no_image'), // TODO abstract_arg
            ])
            ->tag('twig.runtime')

        // Product read helper
        ->set('ekyna_product.twig.helper.product_read', ProductReadHelper::class)
            ->args([
                service('ekyna_product.features'),
                service('ekyna_resource.repository.factory'),
                service('ekyna_resource.helper'),
                service('ekyna_resource.provider.locale'),
                service('translator'),
            ])
            ->tag('twig.runtime')

        // Product extension
        ->set('ekyna_product.twig.extension.product', ProductExtension::class)
            ->tag('twig.extension')

        // Catalog helper
        ->set('ekyna_product.twig.helper.catalog', CatalogHelper::class)
            ->args([
                service('ekyna_product.registry.catalog'),
            ])
            ->tag('twig.runtime')

        // Catalog extension
        ->set('ekyna_product.twig.extension.catalog', CatalogExtension::class)
            ->tag('twig.extension')

        // Highlight extension
        ->set('ekyna_product.twig.extension.highlight', HighlightExtension::class)
            ->tag('twig.extension')

        // Stat extension
        ->set('ekyna_product.twig.extension.stat', StatExtension::class)
            ->tag('twig.extension')
    ;
};
