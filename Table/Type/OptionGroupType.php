<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Component\Table\AbstractTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * OptionGroupType.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupType extends AbstractTableType
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
            ->addColumn('id', 'number', array(
                'sortable' => true,
            ))
            ->addColumn('name', 'anchor', array(
                'label' => 'ekyna_core.field.name',
                'sortable' => true,
                'route_name' => 'ekyna_product_optionGroup_admin_show',
                'route_parameters_map' => array(
                    'optionGroupId' => 'id'
                ),
            ))
            ->addColumn('actions', 'actions', array(
                'buttons' => array(
                    array(
                        'label' => 'ekyna_core.button.edit',
                        'class' => 'warning',
                        'route_name' => 'ekyna_product_optionGroup_admin_edit',
                        'route_parameters_map' => array(
                            'optionGroupId' => 'id'
                        ),
                    ),
                    array(
                        'label' => 'ekyna_core.button.remove',
                        'class' => 'danger',
                        'route_name' => 'ekyna_product_optionGroup_admin_remove',
                        'route_parameters_map' => array(
                            'optionGroupId' => 'id'
                        ),
                    ),
                ),
            ))
            ->addFilter('id', 'number')
            ->addFilter('name', 'text', array(
            	'label' => 'ekyna_core.field.name'
            ))
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
        return 'ekyna_product_optionGroup';
    }
}
