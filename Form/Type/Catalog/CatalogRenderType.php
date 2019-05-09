<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\ContextType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemChoiceType;
use Ekyna\Bundle\ProductBundle\Entity\Catalog;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template\TemplateChoiceType;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('theme', CatalogThemeChoiceType::class);

        if (null === $sale = $options['sale']) {
            if ($options['admin_mode']) {
                $builder
                    ->add('format', ChoiceType::class, [
                        'label'   => 'ekyna_core.field.format',
                        'choices' => CatalogRenderer::getFormats(),
                    ])
                    ->add('displayPrices', ChoiceType::class, [
                        'label'    => 'ekyna_product.catalog.field.display_prices',
                        'choices'  => [
                            'ekyna_core.value.yes' => 1,
                            'ekyna_core.value.no'  => 0,
                        ],
                        'expanded' => true,
                        'attr'     => [
                            'inline'            => true,
                            'align_with_widget' => true,
                        ],
                    ]);
            }

            $builder->add('context', ContextType::class, [
                'label'      => false,
                'admin_mode' => $options['admin_mode'],
            ]);

            return;
        }

        $builder
            ->add('template', TemplateChoiceType::class, [
                'with_slots' => true,
            ])
            ->add('saleItems', SaleItemChoiceType::class, [
                'sale'     => $options['sale'],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', CheckboxType::class, [
                'label'    => 'ekyna_product.catalog.field.save',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Catalog::class,
                'sale'       => null,
            ])
            ->setAllowedTypes('sale', [SaleInterface::class, 'null']);
    }
}
