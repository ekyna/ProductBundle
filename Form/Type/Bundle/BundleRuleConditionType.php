<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BundleRuleChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRuleConditionType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('slot', IntegerType::class, [
                'label' => false,
                'attr'  => ['min' => 0],
            ])
            ->add('choice', IntegerType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['min' => -1],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_bundle_rule_condition';
    }
}
