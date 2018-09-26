<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class CatalogType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogType extends ResourceFormType
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
            ->add('theme', CatalogThemeChoiceType::class)
            ->add('description', TinymceType::class, [
                'label'    => 'ekyna_core.field.description',
                'theme'    => 'simple',
                'required' => false,
            ])
            ->add('pages', CollectionType::class, [
                'label'          => 'ekyna_product.catalog.field.pages',
                'prototype_name' => '__page__',
                'entry_type'     => CatalogPageType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'allow_sort'     => true,
            ]);
    }
}
