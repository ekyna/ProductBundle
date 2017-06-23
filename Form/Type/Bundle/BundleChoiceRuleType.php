<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceRuleTypes;
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
                'choices' => BundleChoiceRuleTypes::getChoices(),
                'select2' => false,
            ])
            ->add('expression', Type\TextType::class, [
                'label' => 'ekyna_product.bundle_choice_rule.field.expression',
            ])
            ->add('position', CollectionPositionType::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_bundle_choice_rule';
    }
}
