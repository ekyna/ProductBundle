<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\ContextType;
use Ekyna\Bundle\ProductBundle\Entity\Catalog;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CatalogRenderType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogRenderType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('format', ChoiceType::class, [
                'label'    => 'ekyna_core.field.format',
                'choices'  => CatalogRenderer::getFormats(),
            ])
            ->add('theme', CatalogThemeChoiceType::class)
            ->add('displayPrices', ChoiceType::class, [
                'label'   => 'ekyna_product.catalog.field.display_prices',
                'choices' => [
                    'ekyna_core.value.yes' => 1,
                    'ekyna_core.value.no'  => 0,
                ],
                'expanded'    => true,
                'attr'        => [
                    'inline'            => true,
                    'align_with_widget' => true,
                ],
            ])
            ->add('context', ContextType::class, [
                'label' => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Catalog::class,
        ]);
    }
}
