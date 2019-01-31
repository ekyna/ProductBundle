<?php

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CategoryType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryType extends ResourceTableType
{
    /**
     * @var ResourceHelper
     */
    private $resourceHelper;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param ResourceHelper        $resourceHelper
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $dataClass
     */
    public function __construct(ResourceHelper $resourceHelper, UrlGeneratorInterface $urlGenerator, $dataClass)
    {
        parent::__construct($dataClass);

        $this->resourceHelper = $resourceHelper;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        // TODO public / editor action buttons

        $builder
            ->addDefaultSort('left')
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([500])
            ->addColumn('name', BType\Column\NestedAnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_category_admin_show',
                'route_parameters_map' => [
                    'categoryId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('visible', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.visible',
                'route_name'           => 'ekyna_product_category_admin_toggle',
                'route_parameters'     => ['field' => 'visible'],
                'route_parameters_map' => ['categoryId' => 'id'],
                'position'             => 20,
            ])
            ->addColumn('visibility', CType\Column\NumberType::class, [
                'label'    => 'ekyna_product.common.visibility',
                'position' => 30,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 40,
            ])
            ->addColumn('actions', BType\Column\NestedActionsType::class, [
                'roots'                 => false,
                'new_child_route'       => 'ekyna_product_category_admin_new_child',
                'move_up_route'         => 'ekyna_product_category_admin_move_up',
                'move_down_route'       => 'ekyna_product_category_admin_move_down',
                'routes_parameters_map' => [
                    'categoryId' => 'id',
                ],
                'buttons'               => [
                    function (RowInterface $row) {
                        $category = $row->getData();

                        if (null !== $path = $this->resourceHelper->generatePublicUrl($category)) {
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
                        $category = $row->getData();

                        if (null !== $path = $this->resourceHelper->generatePublicUrl($category)) {
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
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'icon'                 => 'pencil',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_category_admin_edit',
                        'route_parameters_map' => [
                            'categoryId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'icon'                 => 'trash',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_category_admin_remove',
                        'route_parameters_map' => [
                            'categoryId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ]);
    }
}
