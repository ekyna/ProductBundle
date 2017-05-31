<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates;
use Ekyna\Bundle\CommerceBundle\Table\Column\StockSubjectModeType;
use Ekyna\Bundle\CommerceBundle\Table\Column\StockSubjectStateType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Table\Column\ProductTypeType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
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
     * @var string
     */
    protected $tagClass;


    /**
     * @inheritDoc
     */
    public function __construct($productClass, $brandClass, $categoryClass, $taxGroupClass, $tagClass)
    {
        parent::__construct($productClass);

        $this->brandClass = $brandClass;
        $this->categoryClass = $categoryClass;
        $this->taxGroupClass = $taxGroupClass;
        $this->tagClass = $tagClass;
    }

    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $variantMode = $options['variant_mode'];

        if (!$variantMode) {
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true)
                ->addColumn('type', ProductTypeType::class, [
                    'label'    => 'ekyna_core.field.type',
                    'position' => 5,
                ]);
        } else {
            $builder->setSortable(false);
        }

        $builder
            ->addColumn('designation', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.designation',
                'property_path'        => false,
                'route_name'           => 'ekyna_product_product_admin_show',
                'route_parameters_map' => [
                    'productId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('visible', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_product.product.field.visible',
                'route_name'           => 'ekyna_product_product_admin_toggle',
                'route_parameters'     => ['field' => 'visible'],
                'route_parameters_map' => ['productId' => 'id'],
                'position'             => 20,
            ])
            ->addColumn('reference', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.reference',
                'position' => 30,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'    => 'ekyna_product.product.field.net_price',
                'currency' => 'EUR', // TODO
                'position' => 40,
            ])
            ->addColumn('stockMode', StockSubjectModeType::class, [
                'position' => 50,
            ])
            ->addColumn('stockState', StockSubjectStateType::class, [
                'position' => 60,
            ])
            ->addColumn('tags', TagsType::class, [
                'position' => 100,
            ]);

        if (!$variantMode) {
            $builder
                ->addColumn('categories', DType\Column\EntityType::class, [
                    'label'                => 'ekyna_product.category.label.plural',
                    'entity_label'         => 'name',
                    'route_name'           => 'ekyna_product_category_admin_show',
                    'route_parameters_map' => ['categoryId' => 'id'],
                    'position'             => 70,
                ])
                ->addColumn('brand', DType\Column\EntityType::class, [
                    'label'                => 'ekyna_product.brand.label.singular',
                    'entity_label'         => 'name',
                    'route_name'           => 'ekyna_product_brand_admin_show',
                    'route_parameters_map' => ['brandId' => 'id'],
                    'position'             => 80,
                ])
                ->addColumn('taxGroup', DType\Column\EntityType::class, [
                    'label'                => 'ekyna_commerce.tax_group.label.singular',
                    'entity_label'         => 'name',
                    'route_name'           => 'ekyna_commerce_tax_group_admin_show',
                    'route_parameters_map' => ['taxGroupId' => 'id'],
                    'position'             => 90,
                ]);
        }

        $builder->addColumn('actions', BType\Column\ActionsType::class, [
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
                ->addFilter('type', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_core.field.type',
                    'choices'  => ProductTypes::getChoices([ProductTypes::TYPE_VARIANT]),
                    'position' => 10,
                ])
                ->addFilter('designation', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.designation',
                    'position' => 20,
                ])
                ->addFilter('visible', CType\Filter\BooleanType::class, [
                    'label'    => 'ekyna_product.product.field.visible',
                    'position' => 30,
                ])
                ->addFilter('reference', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.reference',
                    'position' => 40,
                ])
                ->addFilter('netPrice', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_product.product.field.net_price',
                    'position' => 50,
                ])
                ->addFilter('stockMode', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.stock_subject.field.mode',
                    'choices'  => StockSubjectModes::getChoices(),
                    'position' => 60,
                ])
                ->addFilter('stockState', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.stock_subject.field.state',
                    'choices'  => StockSubjectStates::getChoices(),
                    'position' => 70,
                ])
                ->addFilter('category', DType\Filter\EntityType::class, [
                    'label'        => 'ekyna_product.category.label.singular',
                    'class'        => $this->categoryClass,
                    'entity_label' => 'name',
                    'position'     => 80,
                ])
                ->addFilter('brand', DType\Filter\EntityType::class, [
                    'label'        => 'ekyna_product.brand.label.singular',
                    'class'        => $this->brandClass,
                    'entity_label' => 'name',
                    'position'     => 90,
                ])
                ->addFilter('taxGroup', DType\Filter\EntityType::class, [
                    'label'        => 'ekyna_commerce.tax_group.label.singular',
                    'class'        => $this->taxGroupClass,
                    'entity_label' => 'name',
                    'position'     => 100,
                ])
                ->addFilter('tags', DType\Filter\EntityType::class, [
                    'label'        => 'ekyna_cms.tag.label.plural',
                    'class'        => $this->tagClass,
                    'entity_label' => 'name',
                    'position'     => 100,
                ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('variant_mode', false)
            ->setAllowedTypes('variant_mode', 'bool')
            ->setNormalizer('source', function (Options $options, $value) {
                if ($options['variant_mode']) {
                    return $value;
                }

                if (!$value instanceof EntitySource) {
                    throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
                }

                $value->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) {
                    $qb
                        ->andWhere($alias . '.type != :type')
                        ->setParameter('type', ProductTypes::TYPE_VARIANT);
                });

                return $value;
            });
    }
}
