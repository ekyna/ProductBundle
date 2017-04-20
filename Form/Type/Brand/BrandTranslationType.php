<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Brand;

use Ekyna\Bundle\ProductBundle\Entity\BrandTranslation;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BrandTranslationType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Brand
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrandTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => t('field.title', [], 'EkynaUi'),
            ])
            ->add('description', TinymceType::class, [
                'label' => t('field.content', [], 'EkynaUi'),
                'theme' => 'simple',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BrandTranslation::class,
        ]);
    }
}
