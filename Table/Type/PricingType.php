<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PricingType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PricingType extends ResourceTableType
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
                'route_name'           => 'ekyna_product_pricing_admin_show',
                'route_parameters_map' => [
                    'pricingId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_pricing_admin_edit',
                        'route_parameters_map' => ['pricingId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_pricing_admin_remove',
                        'route_parameters_map' => ['pricingId' => 'id'],
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
    public function getName()
    {
        return 'ekyna_product_pricing';
    }
}
