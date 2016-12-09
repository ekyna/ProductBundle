<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttributeGroupType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeGroupType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'id')
            ->addColumn('name', 'anchor', [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_attribute_group_admin_show',
                'route_parameters_map' => ['attributeGroupId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.move_up',
                        'icon'                 => 'arrow-up',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_attribute_group_admin_move_up',
                        'route_parameters_map' => ['attributeGroupId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.move_down',
                        'icon'                 => 'arrow-down',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_attribute_group_admin_move_down',
                        'route_parameters_map' => ['attributeGroupId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_attribute_group_admin_edit',
                        'route_parameters_map' => ['attributeGroupId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_attribute_group_admin_remove',
                        'route_parameters_map' => ['attributeGroupId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', 'text', [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'default_sort' => 'position asc',
            'max_per_page' => 100,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_product_attribute_group';
    }
}
