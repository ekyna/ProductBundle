<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\PriceGrid;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PriceGridRule
 * @package Ekyna\Bundle\ProductBundle\Form\Type\PriceGrid
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceGridRuleType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'label' => t('field.quantity', [], 'EkynaUi'),
                'attr'  => [
                    'min' => 1,
                ],
            ])
            ->add('price', NumberType::class, [
                'label'   => t('field.price', [], 'EkynaUi'),
                'decimal' => true,
                'scale'   => 2,
                'attr'    => [
                    'min' => 0,
                    'max' => 100,
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_price_grid_rule';
    }
}
