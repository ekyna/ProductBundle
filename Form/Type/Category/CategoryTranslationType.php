<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Category;

use Ekyna\Bundle\ProductBundle\Entity\CategoryTranslation;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CategoryTranslationType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Category
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategoryTranslation::class,
        ]);
    }
}
