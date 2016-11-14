<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CategoryType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryType extends ResourceFormType
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
            ->add('parent', EntityType::class, [
                'label'         => 'ekyna_core.field.parent',
                'class'         => $this->dataClass,
                'placeholder'   => 'ekyna_core.field.root',
                'query_builder' => function (EntityRepository $er) {
                    // TODO not the current category
                    return $er
                        ->createQueryBuilder('c')
                        ->orderBy('c.left', 'ASC');
                },
                'choice_label'  => 'name',
                'required'      => false,
            ])
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => BrandTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('media', MediaChoiceType::class, [
                'label'    => 'ekyna_core.field.image',
                'required' => false,
                'types'    => MediaTypes::IMAGE,
            ])
            ->add('seo', SeoType::class, [
                'label' => false,
            ]);
    }
}
