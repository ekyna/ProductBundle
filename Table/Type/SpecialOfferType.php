<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SpecialOfferType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_special_offer_admin_show',
                'route_parameters_map' => [
                    'specialOfferId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.enabled',
                'route_name'           => 'ekyna_product_special_offer_admin_toggle',
                'route_parameters'     => ['field' => 'enabled'],
                'route_parameters_map' => ['specialOfferId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_special_offer_admin_edit',
                        'route_parameters_map' => ['specialOfferId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_special_offer_admin_remove',
                        'route_parameters_map' => ['specialOfferId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ]);
    }
}
