<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\CommerceBundle\Table\Type\AbstractStockUnitType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductStockUnitType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @deprecated Use the StockRenderer
 */
class ProductStockUnitType extends AbstractStockUnitType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        parent::buildTable($builder, $options);

        $builder->addColumn('actions', 'admin_actions', [
            'buttons' => [
                [
                    'label'                => 'ekyna_core.button.edit',
                    'class'                => 'warning',
                    'route_name'           => 'ekyna_product_product_stock_unit_admin_edit',
                    'route_parameters_map' => [
                        'productId'          => 'product.id',
                        'productStockUnitId' => 'id',
                    ],
                    'permission'           => 'edit',
                ],
                [
                    'label'                => 'ekyna_core.button.remove',
                    'class'                => 'danger',
                    'route_name'           => 'ekyna_product_product_stock_unit_admin_remove',
                    'route_parameters_map' => [
                        'productId'          => 'product.id',
                        'productStockUnitId' => 'id',
                    ],
                    'permission'           => 'delete',
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('product')
            ->setDefault('customize_qb', function (Options $options) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
                $product = $options['product'];

                return function (QueryBuilder $qb, $alias) use ($product) {
                    $qb
                        ->andWhere($qb->expr()->notIn($alias . '.state', ':not_state'))
                        ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
                        ->setParameter('not_state', StockUnitStates::STATE_CLOSED)
                        ->setParameter('product', $product);
                };
            })
            ->setAllowedTypes('product', ProductInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_product_product_stock_unit';
    }
}
