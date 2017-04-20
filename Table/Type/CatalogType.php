<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\ProductBundle\Action\Admin\Catalog\RenderAction;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class CatalogType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CatalogType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $source = $builder->getSource();
        if (!$source instanceof EntitySource) {
            throw new UnexpectedTypeException($source, EntitySource::class);
        }

        $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias): void {
            $qb->andWhere($qb->expr()->isNull($alias . '.customer'));
        });

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('title', BType\Column\AnchorType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'position' => 10,
            ])
            /*->addColumn('visible', CType\Column\BooleanType::class, [
                'label'    => t('field.visible', [], 'EkynaUi'),
                'property' => 'visible,
                'position' => 20,
            ])*/
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    RenderAction::class,
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 10,
            ])
            /*->addFilter('visible', CType\Filter\BooleanType::class, [
                'label'    => t('field.visible', [], 'EkynaUi'),
                'position' => 20,
            ])*/
        ;
    }
}
