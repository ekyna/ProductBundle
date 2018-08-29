<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Pricing;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class PricingType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
                ],
            ])
            ->add('designation', TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
                ],
            ])
            ->add('groups', CustomerGroupChoiceType::class, [
                'multiple' => true,
                // TODO 'required' => false,
            ])
            ->add('countries', CountryChoiceType::class, [
                'multiple' => true,
                // TODO 'required' => false,
            ])
            ->add('brands', BrandChoiceType::class, [
                'multiple' => true,
                // TODO 'required' => false,
            ])
            ->add('rules', CollectionType::class, [
                'label'         => 'ekyna_product.pricing.field.rules',
                'entry_type'    => PricingRuleType::class,
                'entry_options' => [],
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\PricingInterface $pricing */
                $pricing = $event->getData();
                /** @see \Ekyna\Bundle\ProductBundle\EventListener\PricingEventSubscriber::onPreUpdate() */
                $pricing->takeSnapshot();
            });
    }
}
