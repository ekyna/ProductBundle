<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\MoveDownAction;
use Ekyna\Bundle\AdminBundle\Action\MoveUpAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class BrandType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BrandType extends AbstractResourceType
{
    private ResourceHelper        $resourceHelper;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(ResourceHelper $resourceHelper, UrlGeneratorInterface $urlGenerator)
    {
        $this->resourceHelper = $resourceHelper;
        $this->urlGenerator = $urlGenerator;
    }

    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        // TODO public / editor action buttons

        $builder
            ->addDefaultSort('position')
            ->setSortable(false)
            ->setFilterable(false)
            ->setPerPageChoices([100])
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('visible', CType\Column\BooleanType::class, [
                'label'    => t('field.visible', [], 'EkynaUi'),
                'property' => 'visible',
                'position' => 20,
            ])
            ->addColumn('visibility', CType\Column\NumberType::class, [
                'label'    => t('common.visibility', [], 'EkynaProduct'),
                'position' => 30,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    MoveUpAction::class,
                    MoveDownAction::class,
                    UpdateAction::class,
                    DeleteAction::class,
                ],
                'buttons'  => [
                    function (RowInterface $row): ?array {
                        $category = $row->getData(null);

                        if (!$path = $this->resourceHelper->generatePublicUrl($category)) {
                            return null;
                        }

                        return [
                            'label'  => t('resource.button.show_front', [], 'EkynaAdmin'),
                            'class'  => 'default',
                            'icon'   => 'eye-open',
                            'target' => '_blank',
                            'path'   => $path,
                        ];
                    },
                    function (RowInterface $row): ?array {
                        $category = $row->getData(null);

                        if (!$path = $this->resourceHelper->generatePublicUrl($category)) {
                            return null;
                        }

                        return [
                            'label'  => t('resource.button.show_editor', [], 'EkynaAdmin'),
                            'class'  => 'default',
                            'icon'   => 'edit',
                            'target' => '_blank',
                            'path'   => $this->urlGenerator->generate('admin_ekyna_cms_editor_index', [
                                'path' => $path,
                            ]),
                        ];
                    },
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('visible', CType\Filter\BooleanType::class, [
                'label'    => t('field.visible', [], 'EkynaUi'),
                'position' => 20,
            ]);
    }
}
