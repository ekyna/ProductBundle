<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BundleChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', ProductSearchType::class, [
                'types' => $options['configurable']
                    ? [
                        ProductTypes::TYPE_SIMPLE,
                        ProductTypes::TYPE_VARIABLE,
                        ProductTypes::TYPE_VARIANT,
                        ProductTypes::TYPE_BUNDLE,
                    ]
                    : [
                        ProductTypes::TYPE_SIMPLE,
                        ProductTypes::TYPE_VARIANT,
                        ProductTypes::TYPE_BUNDLE,
                    ],
            ]);

        if ($options['configurable']) {
            $builder
                ->add('rules', BundleRulesType::class, [
                    'entry_type'     => BundleChoiceRuleType::class,
                    'prototype_name' => '__choice_rule__',
                ])
                ->add('minQuantity', Type\NumberType::class, [
                    'label'   => t('common.min_quantity', [], 'EkynaProduct'),
                    'decimal' => true,
                    'scale'   => 3, // TODO Packaging format
                ])
                ->add('maxQuantity', Type\NumberType::class, [
                    'label'   => t('common.max_quantity', [], 'EkynaProduct'),
                    'decimal' => true,
                    'scale'   => 3, // TODO Packaging format
                ])
                ->add('position', CollectionPositionType::class);
        } else {
            $builder
                ->add('quantity', Type\NumberType::class, [
                    'label'         => t('field.quantity', [], 'EkynaUi'),
                    'property_path' => 'minQuantity',
                    'decimal'       => true,
                    'scale'         => 3, // TODO Packaging format
                ])
                ->add('netPrice', PriceType::class, [
                    'label'    => t('field.net_price', [], 'EkynaCommerce'),
                    'required' => false,
                ])
                ->add('hidden', Type\CheckboxType::class, [
                    'label'    => t('bundle_choice.field.hidden', [], 'EkynaProduct'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('excludeImages', Type\CheckboxType::class, [
                    'label'    => t('bundle_choice.field.exclude_images', [], 'EkynaProduct'),
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);
        }

        $formModifier = function (FormInterface $form, ProductInterface $product = null) {
            $form->add('excludedOptionGroups', BundleChoiceOptionsType::class, [
                'product' => $product,
            ]);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var BundleChoiceInterface $data */
            if (null === $data = $event->getData()) {
                return;
            }

            $formModifier($event->getForm(), $data->getProduct());
        });

        $builder->get('product')
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
                /** @var ProductInterface $data */
                $data = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $data);
            });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['configurable'] = $options['configurable'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('configurable', false)
            ->setAllowedTypes('configurable', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_bundle_choice';
    }
}
