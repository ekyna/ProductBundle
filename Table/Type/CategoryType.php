<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CategoryType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'number')
            ->addColumn('name', 'nested_anchor', [
                'label' => 'ekyna_core.field.name',
                'route_name' => 'ekyna_product_category_admin_show',
                'route_parameters_map' => [
                    'categoryId' => 'id'
                ],
            ])
            ->addColumn('createdAt', 'datetime', [
                'label' => 'ekyna_core.field.created_at',
            ])
            ->addColumn('actions', 'admin_nested_actions', [
                'new_child_route' => 'ekyna_product_category_admin_new_child',
                'move_up_route' => 'ekyna_product_category_admin_move_up',
                'move_down_route' => 'ekyna_product_category_admin_move_down',
                'routes_parameters_map' => [
                    'categoryId' => 'id'
                ],
                'buttons' => [
                    [
                        'label' => 'ekyna_core.button.edit',
                        'icon' => 'pencil',
                        'class' => 'warning',
                        'route_name' => 'ekyna_product_category_admin_edit',
                        'route_parameters_map' => [
                            'categoryId' => 'id'
                        ],
                        'permission' => 'edit',
                    ],
                    [
                        'label' => 'ekyna_core.button.remove',
                        'icon' => 'trash',
                        'class' => 'danger',
                        'route_name' => 'ekyna_product_category_admin_remove',
                        'route_parameters_map' => [
                            'categoryId' => 'id'
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
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'default_sort' => 'left asc',
            'max_per_page' => 100,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_product_category';
    }
}
