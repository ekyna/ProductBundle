<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Pricing;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PricingRuleType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRuleType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('minQuantity', NumberType::class, [
                'label'   => t('common.min_quantity', [], 'EkynaProduct'),
                'decimal' => true,
                'scale'   => 3,
                'attr'    => [
                    'min' => 1,
                ],
            ])
            ->add('percent', NumberType::class, [
                'label'   => t('common.percent', [], 'EkynaProduct'),
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
        return 'ekyna_product_pricing_rule';
    }
}
