<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleRuleConditionsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRuleConditionsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'           => false,
                'sub_widget_col'  => 11,
                'button_col'      => 1,
                'allow_sort'      => false,
                'add_button_text' => 'ekyna_product.bundle_rule.button.add_condition',
                'entry_type'      => BundleRuleConditionType::class,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_bundle_rule_conditions';
    }
}
