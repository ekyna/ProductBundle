<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Table\Column;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductReferenceType
 * @package Ekyna\Bundle\ProductBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReferenceType extends AbstractColumnType
{
    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        /** @var ProductInterface $product */
        $product = $row->getData(null);

        $view->vars['value'] = $product->getReferenceByType($options['type']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('type', ProductReferenceTypes::TYPE_EAN_13)
            ->setAllowedValues('type', ProductReferenceTypes::getConstants());
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
