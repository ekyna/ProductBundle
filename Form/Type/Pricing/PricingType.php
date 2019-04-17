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
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('groups', CustomerGroupChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('countries', CountryChoiceType::class, [
                'enabled'  => false,
                'multiple' => true,
                'required' => false,
            ])
            ->add('rules', CollectionType::class, [
                'label'           => 'ekyna_product.pricing.field.rules',
                'entry_type'      => PricingRuleType::class,
                'entry_options'   => [],
                'prototype_name'  => '__pricing_rule__',
                'allow_add'       => true,
                'allow_delete'    => true,
                'add_button_text' => 'ekyna_product.pricing_rule.button.add',
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\PricingInterface $pricing */
                if (null === $pricing = $event->getData()) {
                    return;
                }
                /** @see \Ekyna\Bundle\ProductBundle\EventListener\PricingListener::onPreUpdate() */
                $pricing->takeSnapshot();
            });

        if (!$options['product_mode']) {
            $builder
                ->add('name', TextType::class, [
                    'label'    => 'ekyna_core.field.name',
                    'required' => false,
                    'attr'     => [
                        'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
                    ],
                ])
                ->add('brands', BrandChoiceType::class, [
                    'multiple' => true,
                ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_pricing';
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'product_mode' => false,
            ])
            ->setAllowedTypes('product_mode', 'bool');
    }
}
