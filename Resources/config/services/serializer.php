<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Service\Serializer\InventoryProductNormalizer;
use Ekyna\Bundle\ProductBundle\Service\Serializer\ProductNormalizer;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Product normalizer
    $services
        ->set('ekyna_product.normalizer.product', ProductNormalizer::class)
        ->call('setCacheManager', [service('liip_imagine.cache.manager')])
        ->call('setSubjectNormalizerHelper', [service('ekyna_commerce.helper.subject_normalizer')])
        ->call('setSupplierProductRepository', [service('ekyna_commerce.repository.supplier_product')]);

    // Inventory product normalizer
    $services
        ->set('ekyna_product.normalizer.inventory_product', InventoryProductNormalizer::class)
        ->tag('serializer.normalizer');
};
