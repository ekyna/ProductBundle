<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ProductBundle\Table\Column\ProductTypeType;
use Ekyna\Bundle\ProductBundle\Table\Type\BrandType;
use Ekyna\Bundle\ProductBundle\Table\Type\CategoryType;
use Ekyna\Bundle\ProductBundle\Table\Type\ProductType;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->set('ekyna_product.table_column_type.product_type', ProductTypeType::class)
            ->args([
                service('ekyna_product.helper.constants'),
            ])
            ->tag('table.column_type')

        ->set('ekyna_product.table_type.product', ProductType::class)
            ->args([
                service('ekyna_resource.helper'),
                service('router'),
            ])
            ->tag('table.type')

        ->set('ekyna_product.table_type.brand', BrandType::class)
            ->args([
                service('ekyna_resource.helper'),
                service('router'),
            ])
            ->tag('table.type')

        ->set('ekyna_product.table_type.category', CategoryType::class)
            ->args([
                service('ekyna_resource.helper'),
                service('router'),
            ])
            ->tag('table.type')
    ;
};
