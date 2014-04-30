<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\AbstractTableType;

/**
 * CategoryType
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryType extends AbstractTableType
{
    protected $entityClass;

    public function __construct($class)
    {
        $this->entityClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $tableBuilder)
    {
        $tableBuilder
            ->addColumn('name', 'nested_anchor', array(
                'label' => 'ekyna_core.field.name',
                'route_name' => 'ekyna_product_category_admin_show',
                'route_parameters_map' => array(
                    'categoryId' => 'id'
                ),
            ))
            ->addColumn('seo.title', 'text', array(
                'label' => 'ekyna_core.field.title',
            ))
            ->addColumn('createdAt', 'datetime', array(
                'label' => 'ekyna_core.field.add_date',
            ))
            ->addColumn('actions', 'nested_actions', array(
                'new_child_route' => 'ekyna_product_category_admin_new_child',
                'move_up_route' => 'ekyna_product_category_admin_move_up',
                'move_down_route' => 'ekyna_product_category_admin_move_down',
                'routes_parameters_map' => array(
                    'categoryId' => 'id'
                ),
                'buttons' => array(
                    array(
                        'label' => 'ekyna_core.button.edit',
                        'icon' => 'pencil',
                        'class' => 'warning',
                        'route_name' => 'ekyna_product_category_admin_edit',
                        'route_parameters_map' => array(
                            'categoryId' => 'id'
                        ),
                    ),
                    array(
                        'label' => 'ekyna_core.button.remove',
                        'icon' => 'trash',
                        'class' => 'danger',
                        'route_name' => 'ekyna_product_category_admin_remove',
                        'route_parameters_map' => array(
                            'categoryId' => 'id'
                        ),
                    ),
                ),
            ))
            ->setDefaultSort('left')
            ->setMaxPerPage(100)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_product_category';
    }
}
