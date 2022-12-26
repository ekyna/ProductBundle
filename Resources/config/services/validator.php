<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Validator\Constraints\BundleStockAdjustmentValidator;
use Ekyna\Bundle\ProductBundle\Validator\Constraints\ProductAttributeValidator;
use Ekyna\Bundle\ProductBundle\Validator\Constraints\ProductTranslationValidator;
use Ekyna\Bundle\ProductBundle\Validator\Constraints\ProductValidator;
use Ekyna\Bundle\ProductBundle\Validator\Constraints\VariantValidator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Bundle stock adjustment constraint validator
    $services
        ->set('ekyna_product.validator.bundle_stock_adjustment', BundleStockAdjustmentValidator::class)
        ->args([
            service('ekyna_product.bundle_stock_adjuster'),
        ])
        ->tag('validator.constraint_validator');

    // Product constraint validator
    $services
        ->set('ekyna_product.validator.product', ProductValidator::class)
        ->args([
            service('ekyna_product.repository.product'),
        ])
        ->tag('validator.constraint_validator');

    // Product translation constraint validator
    $services
        ->set('ekyna_product.validator.product_translation', ProductTranslationValidator::class)
        ->args([
            service('ekyna_product.repository.product_translation'),
        ])
        ->tag('validator.constraint_validator');

    // Variant constraint validator
    $services
        ->set('ekyna_product.validator.variant', VariantValidator::class)
        ->args([
            service('ekyna_resource.provider.locale'),
        ])
        ->tag('validator.constraint_validator');

    // Product attribute constraint validator
    $services
        ->set('ekyna_product.validator.product_attribute', ProductAttributeValidator::class)
        ->args([
            service('ekyna_product.registry.attribute_type'),
        ])
        ->tag('validator.constraint_validator');
};
