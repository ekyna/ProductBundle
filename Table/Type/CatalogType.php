<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CatalogType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CatalogType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
        }

        $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) {
            $qb->andWhere($qb->expr()->isNull($alias . '.customer'));
        });

        $builder
            ->addColumn('title', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_catalog_admin_show',
                'route_parameters_map' => [
                    'catalogId' => 'id',
                ],
                'position'             => 10,
            ])
            /*->addColumn('visible', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.visible',
                'route_name'           => 'ekyna_product_catalog_admin_toggle',
                'route_parameters'     => ['field' => 'visible'],
                'route_parameters_map' => ['catalogId' => 'id'],
                'position'             => 20,
            ])*/
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.display',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_catalog_admin_render',
                        'route_parameters_map' => ['catalogId' => 'id'],
                        'permission'           => 'view',
                    ],
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_catalog_admin_edit',
                        'route_parameters_map' => ['catalogId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_catalog_admin_remove',
                        'route_parameters_map' => ['catalogId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'position' => 10,
            ])
            /*->addFilter('visible', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.visible',
                'position' => 20,
            ])*/;
    }
}
