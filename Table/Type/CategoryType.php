<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CategoryType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addDefaultSort('root')
            ->addDefaultSort('left')
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([100])
            ->addColumn('name', BType\Column\NestedAnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_category_admin_show',
                'route_parameters_map' => [
                    'categoryId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('visible', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.visible',
                'route_name'           => 'ekyna_product_category_admin_toggle',
                'route_parameters'     => ['field' => 'visible'],
                'route_parameters_map' => ['categoryId' => 'id'],
                'position'             => 20,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 30,
            ])
            ->addColumn('actions', BType\Column\NestedActionsType::class, [
                'new_child_route'       => 'ekyna_product_category_admin_new_child',
                'move_up_route'         => 'ekyna_product_category_admin_move_up',
                'move_down_route'       => 'ekyna_product_category_admin_move_down',
                'routes_parameters_map' => [
                    'categoryId' => 'id',
                ],
                'buttons'               => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'icon'                 => 'pencil',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_category_admin_edit',
                        'route_parameters_map' => [
                            'categoryId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'icon'                 => 'trash',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_category_admin_remove',
                        'route_parameters_map' => [
                            'categoryId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ]);
    }
}
