<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Option;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionPositionType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OptionType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Option
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mode', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.mode',
                'choices'  => [
                    'Product' => 'product',
                    'Data'    => 'data',
                ],
                'expanded' => true,
                'attr'     => [
                    'class'             => 'option-mode',
                    'inline'            => true,
                    'align_with_widget' => true,
                ],
            ])
            ->add('product', ProductSearchType::class, [
                'required' => false,
                'types'    => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
                    ProductTypes::TYPE_BUNDLE,
                ],
            ])
            ->add('cascade', Type\CheckboxType::class, [
                'label'    => 'ekyna_product.option.label.plural',
                'required' => false,
                'attr' => [
                    'class' => 'product-cascade',
                ]
            ])
            ->add('designation', Type\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
            ])
            ->add('reference', Type\TextType::class, [
                'label'    => 'ekyna_core.field.reference',
                'required' => false,
                'attr'     => [
                    'placeholder' => 'ekyna_core.field.reference',
                ],
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => OptionTranslationType::class,
                'required'       => false,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('weight', Type\NumberType::class, [
                'label'    => 'ekyna_core.field.weight',
                'required' => false,
                'scale'    => 3,
                'attr'     => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                ],
            ])
            ->add('netPrice', PriceType::class, [
                'label'    => 'ekyna_commerce.field.net_price',
                'required' => false,
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'required' => false,
            ])
            ->add('position', CollectionPositionType::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_option';
    }
}
