<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates;
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
     * @var string
     */
    protected $brandClass;

    /**
     * @var string
     */
    protected $categoryClass;

    /**
     * @var string
     */
    protected $taxGroupClass;


    /**
     * @inheritDoc
     */
    public function __construct($productClass, $brandClass, $categoryClass, $taxGroupClass)
    {
        parent::__construct($productClass);

        $this->brandClass = $brandClass;
        $this->categoryClass = $categoryClass;
        $this->taxGroupClass = $taxGroupClass;
    }

    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $variantMode = null !== $options['variable'];

        $builder->addColumn('id', 'id', [
            'sortable' => !$variantMode,
        ]);

        if (!$variantMode) {
            $builder->addColumn('type', 'ekyna_product_product_type', [
                'label'    => 'ekyna_core.field.type',
                'sortable' => true,
                'position' => 5,
            ]);
        }

        $builder
            ->addColumn('designation', 'anchor', [
                'label'                => 'ekyna_core.field.designation',
                'sortable'             => !$variantMode,
                'property_path'        => null,
                'route_name'           => 'ekyna_product_product_admin_show',
                'route_parameters_map' => [
                    'productId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('visible', 'boolean', [
                'label'                => 'ekyna_product.product.field.visible',
                'sortable'             => !$variantMode,
                'route_name'           => 'ekyna_product_product_admin_toggle',
                'route_parameters'     => ['field' => 'visible'],
                'route_parameters_map' => ['productId' => 'id'],
                'position'             => 20,
            ])
            ->addColumn('reference', 'text', [
                'label'    => 'ekyna_core.field.reference',
                'sortable' => !$variantMode,
                'position' => 30,
            ])
            ->addColumn('netPrice', 'price', [
                'label'    => 'ekyna_product.product.field.net_price',
                'currency' => 'EUR', // TODO
                'sortable' => !$variantMode,
                'position' => 40,
            ])
            ->addColumn('stockMode', 'ekyna_commerce_stock_subject_mode', [
                'position' => 50,
            ])
            ->addColumn('stockState', 'ekyna_commerce_stock_subject_state', [
                'position' => 60,
            ]);

        /*if (!$variantMode) {
            $builder
                ->addColumn('categories', 'anchor', [
                    'label'                => 'ekyna_product.category.label.plural',
                    'property_path'        => 'categories[]',
                    'sortable'             => true,
                    'route_name'           => 'ekyna_product_category_admin_show',
                    'route_parameters_map' => ['categoryId' => 'categories.id'],
                    'position'             => 70,
                ])
                ->addColumn('brand', 'anchor', [
                    'label'                => 'ekyna_product.brand.label.singular',
                    'sortable'             => true,
                    'route_name'           => 'ekyna_product_brand_admin_show',
                    'route_parameters_map' => ['brandId' => 'brand.id'],
                    'position'             => 80,
                ])->addColumn('taxGroup', 'anchor', [
                    'label'                => 'ekyna_commerce.tax_group.label.singular',
                    'sortable'             => true,
                    'route_name'           => 'ekyna_commerce_tax_group_admin_show',
                    'route_parameters_map' => ['taxGroupId' => 'taxGroup.id'],
                    'position'             => 90,
                ])
            ;
        }*/

        $builder->addColumn('actions', 'admin_actions', [
            'buttons' => [
                [
                    'label'                => 'ekyna_core.button.edit',
                    'class'                => 'warning',
                    'route_name'           => 'ekyna_product_product_admin_edit',
                    'route_parameters_map' => ['productId' => 'id'],
                    'permission'           => 'edit',
                ],
                [
                    'label'                => 'ekyna_core.button.remove',
                    'class'                => 'danger',
                    'route_name'           => 'ekyna_product_product_admin_remove',
                    'route_parameters_map' => ['productId' => 'id'],
                    'permission'           => 'delete',
                ],
            ],
        ]);

        if (!$variantMode) {
            $builder
                ->addFilter('type', 'choice', [
                    'label'    => 'ekyna_core.field.type',
                    'choices'  => ProductTypes::getChoices([ProductTypes::TYPE_VARIANT]),
                    'position' => 10,
                ])
                ->addFilter('designation', 'text', [
                    'label'    => 'ekyna_core.field.designation',
                    'position' => 20,
                ])
                ->addFilter('visible', 'boolean', [
                    'label'    => 'ekyna_product.product.field.visible',
                    'position' => 30,
                ])
                ->addFilter('reference', 'text', [
                    'label'    => 'ekyna_core.field.reference',
                    'position' => 40,
                ])
                ->addFilter('netPrice', 'number', [
                    'label'    => 'ekyna_product.product.field.net_price',
                    'position' => 50,
                ])
                ->addFilter('stockMode', 'choice', [
                    'label'    => 'ekyna_commerce.stock_subject.field.mode',
                    'choices'  => StockSubjectModes::getChoices(),
                    'position' => 60,
                ])
                ->addFilter('stockState', 'choice', [
                    'label'    => 'ekyna_commerce.stock_subject.field.state',
                    'choices'  => StockSubjectStates::getChoices(),
                    'position' => 70,
                ])
                ->addFilter('category', 'entity', [
                    'label'    => 'ekyna_product.category.label.singular',
                    'class'    => $this->categoryClass,
                    'property' => 'name',
                    'position' => 80,
                ])
                ->addFilter('brand', 'entity', [
                    'label'    => 'ekyna_product.brand.label.singular',
                    'class'    => $this->brandClass,
                    'property' => 'name',
                    'position' => 90,
                ])
                ->addFilter('taxGroup', 'entity', [
                    'label'    => 'ekyna_commerce.tax_group.label.singular',
                    'class'    => $this->taxGroupClass,
                    'property' => 'name',
                    'position' => 100,
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
