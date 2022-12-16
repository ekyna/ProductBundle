<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Type;

use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class InventoryType
 * @package Ekyna\Bundle\ProductBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'         => t('field.name', [], 'EkynaUi'),
                'property_path' => false,
                'position'      => 10,
            ])
            /*->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
            ])*/;
    }
}
