<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BrandType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
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
            ->add('media', MediaChoiceType::class, [
                'label' => 'ekyna_core.field.image',
                'types' => [MediaTypes::IMAGE],
            ])
            ->add('seo', SeoType::class, [
                'label' => false,
            ]);
    }
}
