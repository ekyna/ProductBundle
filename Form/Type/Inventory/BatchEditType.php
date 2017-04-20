<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

use Ekyna\Bundle\CommerceBundle\Form\StockSubjectFormBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BatchEditType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Inventory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BatchEditType extends AbstractType
{
    private StockSubjectFormBuilder $builder;

    public function __construct(StockSubjectFormBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = ['stockMode', 'quoteOnly', 'endOfLife', 'stockFloor', 'replenishmentTime', 'minimumOrderQuantity'];

        foreach ($fields as $field) {
            $builder
                ->add($field . 'Chk', CheckboxType::class, [
                    'label'    => false,
                    'required' => false,
                    'attr'     => [
                        'data-toggle-field' => $field,
                    ],
                ]);
        }

        $this->builder->initialize($builder);
        $this->builder
            ->addStockMode([
                'required' => true,
            ])
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addStockFloor([
                'required' => true,
            ])
            ->addReplenishmentTime([
                'required' => true,
            ])
            ->addMinimumOrderQuantity([
                'required' => true,
            ]);
    }
}
