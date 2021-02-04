<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaChoiceType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SeparatorType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SeparatorType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'ekyna_core.field.title',
            ])
            ->add('description', TinymceType::class, [
                'label'    => 'ekyna_core.field.description',
                'theme'    => 'simple',
                'required' => false,
            ])
            ->add('image', MediaChoiceType::class, [
                'types'     => [MediaTypes::IMAGE],
                'transform' => true,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return OptionsType::class;
    }
}
