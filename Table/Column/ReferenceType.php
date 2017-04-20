<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Column;

use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

/**
 * Class ReferenceType
 * @package Ekyna\Bundle\ProductBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ReferenceType extends AbstractColumnType
{
    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $view->vars['attr']['data-clipboard-copy'] = (string)$view->vars['value'];
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix(): string
    {
        return 'text';
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return TextType::class;
    }
}
