<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BundleRuleConditionsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRuleConditionsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'           => false,
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => false,
                'add_button_text' => t('bundle_rule.button.add_condition', [], 'EkynaProduct'),
                'entry_type'      => BundleRuleConditionType::class,
            ]);
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_bundle_rule_conditions';
    }
}
