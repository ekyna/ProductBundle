<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CatalogType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => t('field.title', [], 'EkynaUi'),
            ])
            ->add('description', TinymceType::class, [
                'label'    => t('field.description', [], 'EkynaUi'),
                'theme'    => 'simple',
                'required' => false,
            ])
            ->add('pages', CollectionType::class, [
                'label'          => t('catalog.field.pages', [], 'EkynaProduct'),
                'prototype_name' => '__page__',
                'entry_type'     => $options['customer'] ? CatalogCustomerPageType::class : CatalogPageType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'allow_sort'     => true,
            ]);

        if (!$options['customer']) {
            $builder->add('theme', CatalogThemeChoiceType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer', false)
            ->setAllowedTypes('customer', 'bool');
    }
}
