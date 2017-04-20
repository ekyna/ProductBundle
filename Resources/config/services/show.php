<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Show\Type\AttributeConfigType;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->set('ekyna_product.show.type.attribute_config', AttributeConfigType::class)
            ->args([
                service('ekyna_product.registry.attribute_type'),
            ])
            ->tag('ekyna_admin.show.type')
    ;
};
