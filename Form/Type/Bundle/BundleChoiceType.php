<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
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
        $builder->add('product', ProductSearchType::class, [
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
                ->add('rules', CollectionType::class, [
                    'label'           => 'ekyna_product.bundle_choice_rule.label.plural',
                    'sub_widget_col'  => 9,
                    'button_col'      => 3,
                    'allow_sort'      => true,
                    'add_button_text' => 'ekyna_product.bundle_choice_rule.button.add',
                    'entry_type'      => BundleChoiceRuleType::class,
                ])
                ->add('minQuantity', Type\NumberType::class, [
                    'label' => 'ekyna_product.bundle_choice.field.min_quantity',
                ])
                ->add('maxQuantity', Type\NumberType::class, [
                    'label' => 'ekyna_product.bundle_choice.field.max_quantity',
                ])
                ->add('position', Type\HiddenType::class, [
                    'attr' => [
                        'data-collection-role' => 'position',
                    ],
                ]);
        } else {
            $builder->add('quantity', Type\NumberType::class, [
                'label'         => 'ekyna_core.field.quantity',
                'property_path' => 'minQuantity',
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
