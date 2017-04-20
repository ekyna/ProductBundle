<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Category;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as Types;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class CategoryType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Category
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CategoryType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Types\TextType::class, [
                'label'    => t('field.name', [], 'EkynaUi'),
                'required' => true,
            ])
            ->add('visible', Types\CheckboxType::class, [
                'label'    => t('field.visible', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('visibility', Types\IntegerType::class, [
                'label' => t('common.visibility', [], 'EkynaProduct'),
            ])
            ->add('parent', EntityType::class, [
                'label'         => t('field.parent', [], 'EkynaUi'),
                'class'         => $this->dataClass,
                'placeholder'   => t('field.root', [], 'EkynaUi'),
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
                'form_type'      => CategoryTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('media', MediaChoiceType::class, [
                'label'    => t('field.image', [], 'EkynaUi'),
                'required' => false,
                'types'    => [MediaTypes::IMAGE, MediaTypes::SVG],
            ])
            ->add('seo', SeoType::class, [
                'label' => false,
            ]);
    }
}
