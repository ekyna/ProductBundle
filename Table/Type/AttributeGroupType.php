<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

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
            ->addColumn('id', 'number', [
                'sortable' => true,
            ])
            ->addColumn('name', 'anchor', array(
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
                'sortable'             => true,
                'route_name'           => 'ekyna_product_attribute_group_admin_show',
                'route_parameters_map' => ['attributeGroupId' => 'id'],
            ))
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
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
            ->addFilter('id', 'number')
            ->addFilter('name', 'text', [
                'label' => 'ekyna_core.field.name',
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
