<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Brand;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BrandType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Brand
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BrandType extends ResourceFormType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'required' => true,
            ])
            ->add('visible', CheckboxType::class, [
                'label'    => 'ekyna_core.field.visible',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => BrandTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('media', MediaChoiceType::class, [
                'label' => 'ekyna_core.field.image',
                'types' => [MediaTypes::IMAGE, MediaTypes::SVG],
            ])
            ->add('seo', SeoType::class, [
                'label' => false,
            ]);
    }
}
