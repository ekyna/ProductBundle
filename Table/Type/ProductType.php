<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action;
use Ekyna\Bundle\AdminBundle\Table\Type\Filter\ConstantChoiceType;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Subject;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectModes;
use Ekyna\Bundle\CommerceBundle\Model\StockSubjectStates;
use Ekyna\Bundle\CommerceBundle\Table\Column\StockSubjectModeType;
use Ekyna\Bundle\CommerceBundle\Table\Column\StockSubjectStateType;
use Ekyna\Bundle\ProductBundle\Action\Admin\Product;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Table\Column\ProductTypeType;
use Ekyna\Bundle\ProductBundle\Table\Filter\ProductReferenceType;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ProductType
 *
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends AbstractResourceType
{
    protected ResourceHelper        $resourceHelper;
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(
        ResourceHelper        $resourceHelper,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->resourceHelper = $resourceHelper;
        $this->urlGenerator = $urlGenerator;
    }

    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $variantMode = $options['variant_mode'];

        if (!$variantMode) {
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true)
                ->addDefaultSort('id', ColumnSort::DESC)
                ->addColumn('type', ProductTypeType::class, [
                    'label'    => t('field.type', [], 'EkynaUi'),
                    'position' => 10,
                ]);
        } else {
            $builder
                ->setSortable(false)
                ->addDefaultSort('position', ColumnSort::ASC);
        }

        $builder
            ->addColumn('designation', BType\Column\AnchorType::class, [
                'label'         => t('field.designation', [], 'EkynaUi'),
                'property_path' => 'fullDesignation',
                'sortable'      => false,
                'position'      => 20,
            ])
            ->addColumn('visible', CType\Column\BooleanType::class, [
                'label'    => t('field.visible', [], 'EkynaUi'),
                'property' => 'visible',
                'position' => 30,
            ])
            ->addColumn('reference', CType\Column\TextType::class, [
                'label'          => t('field.reference', [], 'EkynaUi'),
                'clipboard_copy' => true,
                'position'       => 40,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'    => t('field.net_price', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addColumn('weight', CType\Column\NumberType::class, [
                'label'     => t('field.weight', [], 'EkynaUi'),
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
                'label'                => t('stock_subject.field.quote_only', [], 'EkynaCommerce'),
                'route_name'           => 'ekyna_product_product_admin_toggle',
                'route_parameters'     => ['field' => 'quoteOnly'],
                'route_parameters_map' => ['productId' => 'id'],
                'position'             => 90,
                'visible'              => false,
            ])
            ->addColumn('endOfLife', CType\Column\BooleanType::class, [
                'label'                => t('stock_subject.field.end_of_life', [], 'EkynaCommerce'),
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
                    'label'        => t('category.label.plural', [], 'EkynaProduct'),
                    'entity_label' => 'name',
                    'position'     => 200,
                    //'visible'              => false,
                ])
                ->addColumn('brand', DType\Column\EntityType::class, [
                    'label'        => t('brand.label.singular', [], 'EkynaProduct'),
                    'entity_label' => 'name',
                    'position'     => 210,
                    //'visible'              => false,
                ])
                ->addColumn('taxGroup', DType\Column\EntityType::class, [
                    'label'        => t('tax_group.label.singular', [], 'EkynaCommerce'),
                    'entity_label' => 'name',
                    'position'     => 220,
                    //'visible'              => false,
                ]);
        }

        $actions = $buttons = [];
        if ($variantMode) {
            $actions[] = Product\MoveUpAction::class;
            $actions[] = Product\MoveDownAction::class;
        } else {
            $buttons[] = function (RowInterface $row): ?array {
                $product = $row->getData(null);

                if (null !== $path = $this->resourceHelper->generatePublicUrl($product)) {
                    return [
                        'label'  => 'ekyna_admin.resource.button.show_front',
                        'class'  => 'default',
                        'icon'   => 'eye-open',
                        'target' => '_blank',
                        'path'   => $path,
                    ];
                }

                return null;
            };
            $buttons[] = function (RowInterface $row): ?array {
                $product = $row->getData(null);

                if (null !== $path = $this->resourceHelper->generatePublicUrl($product)) {
                    return [
                        'label'  => 'ekyna_admin.resource.button.show_editor',
                        'class'  => 'default',
                        'icon'   => 'edit',
                        'target' => '_blank',
                        'path'   => $this->urlGenerator->generate('admin_ekyna_cms_editor_index', [
                            'path' => $path,
                        ]),
                    ];
                }

                return null;
            };
        }
        $actions[] = Subject\LabelAction::class;
        $actions[] = Action\UpdateAction::class;
        $actions[] = Action\DeleteAction::class;

        $builder->addColumn('actions', BType\Column\ActionsType::class, [
            'resource' => $this->dataClass,
            'actions'  => $actions,
            'buttons'  => $buttons,
        ]);

        if (!$variantMode) {
            $builder
                ->addFilter('type', ConstantChoiceType::class, [
                    'label'    => t('field.type', [], 'EkynaUi'),
                    'class'    => ProductTypes::class,
                    'filter'   => [ProductTypes::TYPE_VARIANT],
                    'position' => 10,
                ])
                ->addFilter('designation', CType\Filter\TextType::class, [
                    'label'    => t('field.designation', [], 'EkynaUi'),
                    'position' => 20,
                ])
                ->addFilter('visible', CType\Filter\BooleanType::class, [
                    'label'    => t('field.visible', [], 'EkynaUi'),
                    'position' => 30,
                ])
                ->addFilter('reference', ProductReferenceType::class, [
                    'label'    => t('field.reference', [], 'EkynaUi'),
                    'position' => 40,
                ])
                ->addFilter('netPrice', CType\Filter\NumberType::class, [
                    'label'    => t('field.net_price', [], 'EkynaCommerce'),
                    'position' => 50,
                ])
                ->addFilter('weight', CType\Filter\NumberType::class, [
                    'label'    => t('field.weight', [], 'EkynaUi'),
                    'position' => 60,
                ])
                ->addFilter('stockMode', ConstantChoiceType::class, [
                    'label'    => t('stock_subject.field.mode', [], 'EkynaCommerce'),
                    'class'    => StockSubjectModes::class,
                    'position' => 70,
                ])
                ->addFilter('stockState', ConstantChoiceType::class, [
                    'label'    => t('stock_subject.field.state', [], 'EkynaCommerce'),
                    'class'    => StockSubjectStates::class,
                    'position' => 80,
                ])
                ->addFilter('physical', CType\Filter\BooleanType::class, [
                    'label'    => t('field.physical', [], 'EkynaCommerce'),
                    'position' => 90,
                ])
                ->addFilter('quoteOnly', CType\Filter\BooleanType::class, [
                    'label'    => t('stock_subject.field.quote_only', [], 'EkynaCommerce'),
                    'position' => 95,
                ])
                ->addFilter('endOfLife', CType\Filter\BooleanType::class, [
                    'label'    => t('stock_subject.field.end_of_life', [], 'EkynaCommerce'),
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
                ->addFilter('pricingGroup', ResourceType::class, [
                    'resource' => 'ekyna_product.pricing_group',
                    'position' => 220,
                ])
                ->addFilter('attributeSet', ResourceType::class, [
                    'resource' => 'ekyna_product.attribute_set',
                    'position' => 230,
                ])
                ->addFilter('taxGroup', ResourceType::class, [
                    'resource' => 'ekyna_commerce.tax_group',
                    'position' => 240,
                ])
                ->addFilter('tags', ResourceType::class, [
                    'resource' => 'ekyna_cms.tag',
                    'position' => 998,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'variant_mode'     => false,
                'resource_summary' => true,
            ])
            ->setAllowedTypes('variant_mode', 'bool')
            ->setNormalizer('source', function (Options $options, $value) {
                if ($options['variant_mode']) {
                    return $value;
                }

                if (!$value instanceof EntitySource) {
                    throw new UnexpectedTypeException($value, EntitySource::class);
                }

                $value->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias): void {
                    $qb
                        ->andWhere($alias.'.type != :type')
                        ->setParameter('type', ProductTypes::TYPE_VARIANT);
                });

                return $value;
            });
    }
}
