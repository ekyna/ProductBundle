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
use Ekyna\Bundle\ProductBundle\Table\Column\ReferenceType;
use Ekyna\Bundle\ProductBundle\Table\Filter\ProductReferenceType;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Ekyna\Component\Table\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ProductType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends ResourceTableType
{
    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     * @param UrlGeneratorInterface  $urlGenerator
     * @param string                 $productClass
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        UrlGeneratorInterface $urlGenerator,
        string $productClass
    ) {
        parent::__construct($productClass);

        $this->subjectHelper = $subjectHelper;
        $this->urlGenerator = $urlGenerator;
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
                ->addDefaultSort('id', ColumnSort::DESC)
                ->addColumn('type', ProductTypeType::class, [
                    'label'    => 'ekyna_core.field.type',
                    'position' => 10,
                ]);
        } else {
            $builder
                ->setSortable(false)
                ->addDefaultSort('position', ColumnSort::ASC);
        }

        $builder
            ->addColumn('designation', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.designation',
                'property_path'        => 'fullDesignation',
                'sortable'             => false,
                'route_name'           => 'ekyna_product_product_admin_show',
                'route_parameters_map' => [
                    'productId' => 'id',
                ],
                'position'             => 20,
            ])
            ->addColumn('visible', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.visible',
                'route_name'           => 'ekyna_product_product_admin_toggle',
                'route_parameters'     => ['field' => 'visible'],
                'route_parameters_map' => ['productId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('reference', ReferenceType::class, [
                'label'    => 'ekyna_core.field.reference',
                'position' => 40,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'    => 'ekyna_commerce.field.net_price',
                'position' => 50,
            ])
            ->addColumn('weight', CType\Column\NumberType::class, [
                'label'     => 'ekyna_core.field.weight',
                'precision' => 3,
                'append'    => 'Kg',
                'position'  => 60,
                //'visible'   => false,
            ])
            ->addColumn('stockMode', StockSubjectModeType::class, [
                'position' => 70,
                //'visible'  => false,
            ])
            ->addColumn('stockState', StockSubjectStateType::class, [
                'position' => 80,
            ])
            /*->addColumn('quoteOnly', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_commerce.stock_subject.field.quote_only',
                'route_name'           => 'ekyna_product_product_admin_toggle',
                'route_parameters'     => ['field' => 'quoteOnly'],
                'route_parameters_map' => ['productId' => 'id'],
                'position'             => 90,
                'visible'              => false,
            ])
            ->addColumn('endOfLife', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_commerce.stock_subject.field.end_of_life',
                'route_name'           => 'ekyna_product_product_admin_toggle',
                'route_parameters'     => ['field' => 'endOfLife'],
                'route_parameters_map' => ['productId' => 'id'],
                'position'             => 100,
                'visible'              => false,
            ])*/
            ->addColumn('tags', TagsType::class, [
                'position' => 998,
            ]);

        if (!$variantMode) {
            $builder
                ->addColumn('categories', DType\Column\EntityType::class, [
                    'label'                => 'ekyna_product.category.label.plural',
                    'entity_label'         => 'name',
                    'route_name'           => 'ekyna_product_category_admin_show',
                    'route_parameters_map' => ['categoryId' => 'id'],
                    'position'             => 200,
                    //'visible'              => false,
                ])
                ->addColumn('brand', DType\Column\EntityType::class, [
                    'label'                => 'ekyna_product.brand.label.singular',
                    'entity_label'         => 'name',
                    'route_name'           => 'ekyna_product_brand_admin_show',
                    'route_parameters_map' => ['brandId' => 'id'],
                    'position'             => 210,
                    //'visible'              => false,
                ])
                ->addColumn('taxGroup', DType\Column\EntityType::class, [
                    'label'                => 'ekyna_commerce.tax_group.label.singular',
                    'entity_label'         => 'name',
                    'route_name'           => 'ekyna_commerce_tax_group_admin_show',
                    'route_parameters_map' => ['taxGroupId' => 'id'],
                    'position'             => 220,
                    //'visible'              => false,
                ]);
        }

        if ($variantMode) {
            $buttons = [
                [
                    'label'                => 'ekyna_core.button.move_up',
                    'icon'                 => 'arrow-up',
                    'class'                => 'primary',
                    'route_name'           => 'ekyna_product_product_admin_move_up',
                    'route_parameters_map' => ['productId' => 'id'],
                    'permission'           => 'edit',
                ],
                [
                    'label'                => 'ekyna_core.button.move_down',
                    'icon'                 => 'arrow-down',
                    'class'                => 'primary',
                    'route_name'           => 'ekyna_product_product_admin_move_down',
                    'route_parameters_map' => ['productId' => 'id'],
                    'permission'           => 'edit',
                ],
            ];
        } else {
            $buttons = [
                function (RowInterface $row) {
                    $product = $row->getData();

                    if (null !== $path = $this->subjectHelper->generatePublicUrl($product)) {
                        return [
                            'label'  => 'ekyna_admin.resource.button.show_front',
                            'class'  => 'default',
                            'icon'   => 'eye-open',
                            'target' => '_blank',
                            'path'   => $path,
                        ];
                    }

                    return null;
                },
                function (RowInterface $row) {
                    $product = $row->getData();

                    if (null !== $path = $this->subjectHelper->generatePublicUrl($product)) {
                        return [
                            'label'  => 'ekyna_admin.resource.button.show_editor',
                            'class'  => 'default',
                            'icon'   => 'edit',
                            'target' => '_blank',
                            'path'   => $this->urlGenerator->generate('ekyna_cms_editor_index', [
                                'path' => $path,
                            ]),
                        ];
                    }

                    return null;
                },
            ];
        }
        $buttons[] = [
            'label'                => 'ekyna_product.product.button.label',
            'icon'                 => 'barcode',
            'class'                => 'primary',
            'route_name'           => 'ekyna_product_product_admin_label',
            'route_parameters'     => ['format' => 'large'],
            'route_parameters_map' => ['id' => 'id'],
            'permission'           => 'edit',
        ];
        $buttons[] = [
            'label'                => 'ekyna_core.button.edit',
            'icon'                 => 'pencil',
            'class'                => 'warning',
            'route_name'           => 'ekyna_product_product_admin_edit',
            'route_parameters_map' => ['productId' => 'id'],
            'permission'           => 'edit',
        ];
        $buttons[] = [
            'label'                => 'ekyna_core.button.remove',
            'icon'                 => 'trash',
            'class'                => 'danger',
            'route_name'           => 'ekyna_product_product_admin_remove',
            'route_parameters_map' => ['productId' => 'id'],
            'permission'           => 'delete',
        ];

        $builder->addColumn('actions', BType\Column\ActionsType::class, [
            'buttons' => $buttons,
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
                    'label'    => 'ekyna_core.field.visible',
                    'position' => 30,
                ])
                ->addFilter('reference', ProductReferenceType::class, [
                    'label'    => 'ekyna_core.field.reference',
                    'position' => 40,
                ])
                ->addFilter('netPrice', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.field.net_price',
                    'position' => 50,
                ])
                ->addFilter('weight', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_core.field.weight',
                    'position' => 60,
                ])
                ->addFilter('stockMode', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.stock_subject.field.mode',
                    'choices'  => StockSubjectModes::getChoices(),
                    'position' => 70,
                ])
                ->addFilter('stockState', CType\Filter\ChoiceType::class, [
                    'label'    => 'ekyna_commerce.stock_subject.field.state',
                    'choices'  => StockSubjectStates::getChoices(),
                    'position' => 80,
                ])
                ->addFilter('quoteOnly', CType\Filter\BooleanType::class, [
                    'label'    => 'ekyna_commerce.stock_subject.field.quote_only',
                    'position' => 90,
                ])
                ->addFilter('endOfLife', CType\Filter\BooleanType::class, [
                    'label'    => 'ekyna_commerce.stock_subject.field.end_of_life',
                    'position' => 100,
                ])
                ->addFilter('categories', ResourceType::class, [
                    'resource' => 'ekyna_product.category',
                    'position' => 200,
                ])
                ->addFilter('brand', ResourceType::class, [
                    'resource' => 'ekyna_product.brand',
                    'position' => 210,
                ])
                ->addFilter('attributeSet', ResourceType::class, [
                    'resource' => 'ekyna_product.attribute_set',
                    'position' => 220,
                ])
                ->addFilter('taxGroup', ResourceType::class, [
                    'resource' => 'ekyna_commerce.tax_group',
                    'position' => 230,
                ])
                ->addFilter('tags', ResourceType::class, [
                    'resource' => 'ekyna_cms.tag',
                    'position' => 998,
                ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function buildRowView(View\RowView $view, RowInterface $row, array $options)
    {
        $view->vars['attr']['data-summary'] = json_encode([
            'route'      => 'ekyna_product_product_admin_summary',
            'parameters' => ['productId' => $row->getData('id')],
        ]);
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
