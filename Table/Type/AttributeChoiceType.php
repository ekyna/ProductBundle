<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class AttributeChoiceType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeChoiceType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addDefaultSort('position')
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([100])
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_attribute_choice_admin_show',
                'route_parameters_map' => ['attributeId' => 'attribute.id', 'attributeChoiceId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.move_up',
                        'icon'                 => 'arrow-up',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_attribute_choice_admin_move_up',
                        'route_parameters_map' => ['attributeId' => 'attribute.id', 'attributeChoiceId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.move_down',
                        'icon'                 => 'arrow-down',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_attribute_choice_admin_move_down',
                        'route_parameters_map' => ['attributeId' => 'attribute.id', 'attributeChoiceId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_attribute_choice_admin_edit',
                        'route_parameters_map' => ['attributeId' => 'attribute.id', 'attributeChoiceId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_attribute_choice_admin_remove',
                        'route_parameters_map' => ['attributeId' => 'attribute.id', 'attributeChoiceId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ]);
    }
}
