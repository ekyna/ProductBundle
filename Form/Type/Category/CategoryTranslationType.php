<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Category;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\ProductBundle\Entity\CategoryTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CategoryTranslationType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Category
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryTranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'ekyna_core.field.title',
            ])
            ->add('description', TinymceType::class, [
                'label' => 'ekyna_core.field.content',
                'theme' => 'simple',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CategoryTranslation::class,
        ]);
    }
}
