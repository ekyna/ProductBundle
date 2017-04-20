<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\ProductBundle\Model\BundleRuleTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class BundleSlotRuleType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotRuleType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ConstantChoiceType::class, [
                'label'   => t('field.type', [], 'EkynaUi'),
                'class'   => BundleRuleTypes::class,
                'select2' => false,
            ])
            ->add('conditions', BundleRuleConditionsType::class, [
                'prototype_name' => '__slot_rule_condition__',
            ])
            ->add('position', CollectionPositionType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_bundle_rule';
    }
}
