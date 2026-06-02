<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\PriceGrid;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PriceGridType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\PriceGrid
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceGridType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('group', CustomerGroupChoiceType::class)
            ->add('rules', CollectionType::class, [
                'label'           => t('pricing.field.rules', [], 'EkynaProduct'),
                'entry_type'      => PriceGridRuleType::class,
                'entry_options'   => [],
                'prototype_name'  => '__price_grid_rule__',
                'allow_add'       => true,
                'allow_delete'    => true,
                'add_button_text' => t('button.add', [], 'EkynaUi'),
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_price_grid';
    }
}
