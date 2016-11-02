<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $variantMode = null !== $options['variable'];

        $builder
            ->addColumn('id', 'number', [
                'sortable' => !$variantMode,
            ]);

        if (!$variantMode) {
            $builder
                ->addColumn('type', 'ekyna_product_product_type', [
                    'label'    => 'ekyna_core.field.type',
                    'sortable' => true,
                ]);
        }

        $builder
            ->addColumn('designation', 'anchor', [
                'label'                => 'ekyna_core.field.designation',
                'sortable'             => !$variantMode,
                'route_name'           => 'ekyna_product_product_admin_show',
                'route_parameters_map' => [
                    'productId' => 'id',
                ],
            ])
            ->addColumn('reference', 'text', [
                'label'    => 'ekyna_core.field.reference',
                'sortable' => !$variantMode,
            ])
            ->addColumn('netPrice', 'price', [
                'label'    => 'ekyna_product.product.field.net_price',
                'currency' => 'EUR', // TODO
                'sortable' => !$variantMode,
            ])
            ->addColumn('taxGroup', 'anchor', [
                'label'                => 'ekyna_product.tax_group.label.singular',
                'sortable'             => !$variantMode,
                'route_name'           => 'ekyna_product_tax_group_admin_show',
                'route_parameters_map' => [
                    'taxGroupId' => 'taxGroup.id',
                ],
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_product_admin_edit',
                        'route_parameters_map' => [
                            'productId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_product_admin_remove',
                        'route_parameters_map' => [
                            'productId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ]);

        if (null === $options['variable']) {
            $builder
                ->addFilter('designation', 'text', [
                    'label' => 'ekyna_core.field.designation',
                ])
                ->addFilter('netPrice', 'number', [
                    'label' => 'ekyna_core.field.net_price',
                ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'variable'     => null,
            'customize_qb' => function (Options $options) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $variable */
                if (null !== $variable = $options['variable']) {
                    ProductTypes::assertVariable($variable);

                    return function (QueryBuilder $qb, $alias) use ($variable) {
                        $qb
                            ->andWhere($alias . '.parent = :parent')
                            ->andWhere($alias . '.type = :type')
                            ->setParameter('parent', $variable)
                            ->setParameter('type', ProductTypes::TYPE_VARIANT);
                    };
                }

                return function (QueryBuilder $qb, $alias) {
                    $qb
                        ->andWhere($alias . '.type != :type')
                        ->setParameter('type', ProductTypes::TYPE_VARIANT);
                };
            },
        ]);

        $resolver
            ->setAllowedTypes('variable', ['null', 'Ekyna\Bundle\ProductBundle\Model\ProductInterface']);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_product_product';
    }
}
