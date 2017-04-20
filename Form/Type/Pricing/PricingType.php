<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Pricing;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class PricingType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Pricing
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                'label'           => t('pricing.field.rules', [], 'EkynaProduct'),
                'entry_type'      => PricingRuleType::class,
                'entry_options'   => [],
                'prototype_name'  => '__pricing_rule__',
                'allow_add'       => true,
                'allow_delete'    => true,
                'add_button_text' => t('pricing_rule.button.add', [], 'EkynaProduct'),
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
                    'label'    => t('field.name', [], 'EkynaUi'),
                    'required' => false,
                    'attr'     => [
                        'help_text' => t('leave_blank_to_auto_generate', [], 'EkynaProduct'),
                    ],
                ])
                ->add('brands', BrandChoiceType::class, [
                    'multiple' => true,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'product_mode' => false,
            ])
            ->setAllowedTypes('product_mode', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_pricing';
    }
}
