<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class BrandType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class BrandType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'number')
            ->addColumn('name', 'anchor', [
                'label' => 'ekyna_core.field.name',
                'sortable' => true,
                'route_name' => 'ekyna_product_brand_admin_show',
                'route_parameters_map' => [
                    'brandId' => 'id'
                ],
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.move_up',
                        'icon'                 => 'arrow-up',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_brand_admin_move_up',
                        'route_parameters_map' => [
                            'brandId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.move_down',
                        'icon'                 => 'arrow-down',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_brand_admin_move_down',
                        'route_parameters_map' => [
                            'brandId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label' => 'ekyna_core.button.edit',
                        'class' => 'warning',
                        'route_name' => 'ekyna_product_brand_admin_edit',
                        'route_parameters_map' => [
                            'brandId' => 'id'
                        ],
                        'permission' => 'edit',
                    ],
                    [
                        'label' => 'ekyna_core.button.remove',
                        'class' => 'danger',
                        'route_name' => 'ekyna_product_brand_admin_remove',
                        'route_parameters_map' => [
                            'brandId' => 'id'
                        ],
                        'permission' => 'delete',
                    ],
                ],
            ])
            ->addFilter('id', 'number')
            ->addFilter('name', 'text', [
                'label' => 'ekyna_core.field.name'
            ])
        ;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_product_brand';
    }
}
