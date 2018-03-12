<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\ProductBundle\Entity\ProductTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductTranslationType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $titleOptions = [
            'label' => 'ekyna_core.field.title',
        ];
        if ($options['variant_mode']) {
            $titleOptions['required'] = false;
            $titleOptions['attr'] = [
                'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
            ];
        }

        $builder
            ->add('title', TextType::class, $titleOptions)
            ->add('subTitle', TextType::class, [
                'label'    => 'ekyna_core.field.subtitle',
                'required' => false,
            ])
            ->add('description', TinymceType::class, [
                'label'    => 'ekyna_core.field.content',
                'theme'    => 'front',
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'   => ProductTranslation::class,
                'variant_mode' => false,
            ])
            ->setAllowedTypes('variant_mode', 'bool');
    }
}
