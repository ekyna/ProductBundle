<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\ProductBundle\Entity\ProductTranslation;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ProductTranslationType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $titleOptions = [
            'label' => t('field.title', [], 'EkynaUi'),
        ];
        if ($options['variant_mode']) {
            $titleOptions['required'] = false;
            $titleOptions['attr'] = [
                'help_text' => t('leave_blank_to_auto_generate', [], 'EkynaProduct'),
            ];
        }

        $builder
            ->add('title', TextType::class, $titleOptions)
            ->add('subTitle', TextType::class, [
                'label'    => t('field.subtitle', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('description', TinymceType::class, [
                'label'    => t('field.description', [], 'EkynaUi'),
                'theme'    => 'simple',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class'   => ProductTranslation::class,
                'variant_mode' => false,
            ])
            ->setAllowedTypes('variant_mode', 'bool');
    }
}
