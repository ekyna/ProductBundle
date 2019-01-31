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
 * Class BrandType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BrandType extends ResourceTableType
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
            ->addDefaultSort('position')
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([100])
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_product_brand_admin_show',
                'route_parameters_map' => [
                    'brandId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('visible', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.visible',
                'route_name'           => 'ekyna_product_brand_admin_toggle',
                'route_parameters'     => ['field' => 'visible'],
                'route_parameters_map' => ['brandId' => 'id'],
                'position'             => 20,
            ])
            ->addColumn('visibility', CType\Column\NumberType::class, [
                'label'    => 'ekyna_product.common.visibility',
                'position' => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
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
                        'label'                => 'ekyna_core.button.move_up',
                        'icon'                 => 'arrow-up',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_brand_admin_move_up',
                        'route_parameters_map' => ['brandId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.move_down',
                        'icon'                 => 'arrow-down',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_product_brand_admin_move_down',
                        'route_parameters_map' => ['brandId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_product_brand_admin_edit',
                        'route_parameters_map' => ['brandId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_product_brand_admin_remove',
                        'route_parameters_map' => ['brandId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('visible', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.visible',
                'position' => 20,
            ]);
    }
}
