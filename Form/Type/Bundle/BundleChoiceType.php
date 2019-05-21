<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceType extends ResourceFormType
{
    /**
     * @var string
     */
    protected $productClass;


    /**
     * Constructor.
     *
     * @param string $bundleChoiceClass
     * @param string $productClass
     */
    public function __construct($bundleChoiceClass, $productClass)
    {
        parent::__construct($bundleChoiceClass);

        $this->productClass = $productClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO only visible products for configurable
        $builder->add('product', ProductSearchType::class, [
            // 'visible' => $options['configurable'],
            'types' => $options['configurable'] ? [
                ProductTypes::TYPE_SIMPLE,
                ProductTypes::TYPE_VARIABLE,
                ProductTypes::TYPE_VARIANT,
                ProductTypes::TYPE_BUNDLE,
            ] : [
                ProductTypes::TYPE_SIMPLE,
                ProductTypes::TYPE_VARIANT,
                ProductTypes::TYPE_BUNDLE,
            ],
        ]);

        if ($options['configurable']) {
            // TODO options ( + fixed/user defined)
            $builder
                ->add('rules', BundleRulesType::class, [
                    'entry_type' => BundleChoiceRuleType::class,
                    'prototype_name'  => '__choice_rule__',
                ])
                ->add('minQuantity', Type\NumberType::class, [
                    'label' => 'ekyna_product.common.min_quantity',
                    'scale' => 3, // TODO Packaging
                ])
                ->add('maxQuantity', Type\NumberType::class, [
                    'label' => 'ekyna_product.common.max_quantity',
                    'scale' => 3, // TODO Packaging
                ])
                ->add('position', CollectionPositionType::class);
        } else {
            $builder
                ->add('quantity', Type\NumberType::class, [
                    'label'         => 'ekyna_core.field.quantity',
                    'property_path' => 'minQuantity',
                    'scale'         => 3, // TODO Packaging
                ])
                ->add('netPrice', PriceType::class, [
                    'label'    => 'ekyna_commerce.field.net_price',
                    'required' => false,
                ])
                ->add('hidden', Type\CheckboxType::class, [
                    'label'    => 'ekyna_product.bundle_choice.field.hidden',
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ])
                ->add('useOptions', Type\CheckboxType::class, [
                    'label'    => 'ekyna_product.bundle_choice.field.use_options',
                    'required' => false,
                    'attr'     => [
                        'align_with_widget' => true,
                    ],
                ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['configurable'] = $options['configurable'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('configurable', false)
            ->setAllowedTypes('configurable', 'bool');
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_bundle_choice';
    }
}
