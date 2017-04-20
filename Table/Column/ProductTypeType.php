<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Column;

use Ekyna\Bundle\ProductBundle\Service\ConstantsHelper;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

/**
 * Class ProductTypeType
 * @package Ekyna\Bundle\ProductBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypeType extends AbstractColumnType
{
    private ConstantsHelper $constantHelper;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     */
    public function __construct(ConstantsHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $view->vars['value'] = $this->constantHelper->renderProductTypeBadge($view->vars['value'], false);
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
        return PropertyType::class;
    }
}
