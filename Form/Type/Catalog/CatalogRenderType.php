<?php

declare(strict_types=1);

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

use function Symfony\Component\Translation\t;

/**
 * Class CatalogRenderType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogRenderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('theme', CatalogThemeChoiceType::class);

        if (null === $sale = $options['sale']) {
            if ($options['admin_mode']) {
                $builder
                    ->add('format', ChoiceType::class, [
                        'label'   => t('field.format', [], 'EkynaUi'),
                        'choices' => CatalogRenderer::getFormats(),
                    ])
                    ->add('displayPrices', ChoiceType::class, [
                        'label'                     => t('catalog.field.display_prices', [], 'EkynaProduct'),
                        'choices'                   => [
                            'value.yes' => 1,
                            'value.no'  => 0,
                        ],
                        'choice_translation_domain' => 'EkynaUi',
                        'expanded'                  => true,
                        'attr'                      => [
                            'inline'            => true,
                            'align_with_widget' => true,
                        ],
                    ]);
            }

            $builder->add('context', ContextType::class, [
                'label' => false,
            ]);

            return;
        }

        $builder
            ->add('template', TemplateChoiceType::class, [
                'with_slots' => true,
            ])
            ->add('displayPrices', ChoiceType::class, [
                'label'                     => t('catalog.field.display_prices', [], 'EkynaProduct'),
                'choices'                   => [
                    'value.yes' => 1,
                    'value.no'  => 0,
                ],
                'choice_translation_domain' => 'EkynaUi',
                'expanded'                  => true,
                'attr'                      => [
                    'inline'            => true,
                    'align_with_widget' => true,
                ],
            ])
            ->add('saleItems', SaleItemChoiceType::class, [
                'sale'     => $sale,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('save', CheckboxType::class, [
                'label'    => t('catalog.field.save', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Catalog::class,
                'sale'       => null,
            ])
            ->setAllowedTypes('sale', [SaleInterface::class, 'null']);
    }
}
