<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\ProductBundle\Model\BundleRuleTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BundleChoiceRuleType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceRuleType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', Type\ChoiceType::class, [
                'label'   => 'ekyna_core.field.type',
                'choices' => BundleRuleTypes::getChoices([
                    BundleRuleTypes::REQUIRED_IF_ALL,
                    BundleRuleTypes::REQUIRED_IF_ANY
                ], 0),
                'select2' => false,
            ])
            ->add('conditions', BundleRuleConditionsType::class, [
                'prototype_name' => '__choice_rule_condition__',
            ])
            ->add('position', CollectionPositionType::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_bundle_rule';
    }
}
