<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Option;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\PriceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupChoiceType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionPositionType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class OptionType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Option
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode', Type\ChoiceType::class, [
                'label'    => t('field.mode', [], 'EkynaUi'),
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
                'label'    => t('option.label.plural', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'class' => 'product-cascade',
                ],
            ])
            ->add('designation', Type\TextType::class, [
                'label'    => t('field.designation', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'placeholder' => t('field.designation', [], 'EkynaUi'),
                ],
            ])
            ->add('reference', Type\TextType::class, [
                'label'    => t('field.reference', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'placeholder' => t('field.reference', [], 'EkynaUi'),
                ],
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => OptionTranslationType::class,
                'required'       => false,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('weight', Type\NumberType::class, [
                'label'    => t('field.weight', [], 'EkynaUi'),
                'required' => false,
                'decimal'  => true,
                'scale'    => 3,
                'attr'     => [
                    'placeholder' => t('field.weight', [], 'EkynaUi'),
                    'input_group' => ['append' => 'kg'],
                ],
            ])
            ->add('netPrice', PriceType::class, [
                'label'    => t('field.net_price', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'required' => false,
            ])
            ->add('position', CollectionPositionType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_option';
    }
}
