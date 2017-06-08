<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\ProductBundle\Entity\BundleSlotTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleSlotTranslationType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotTranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label'        => 'ekyna_core.field.title',
//                'admin_helper' => 'CMS_PAGE_TITLE',
            ))
            ->add('description', TinymceType::class, array(
                'label'        => 'ekyna_core.field.content',
//                'admin_helper' => 'CMS_PAGE_CONTENT',
                'theme'        => 'front'
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => BundleSlotTranslation::class,
        ));
    }
}
