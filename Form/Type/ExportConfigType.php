<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\ContextType;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_flip;
use function Symfony\Component\Translation\t;

/**
 * Class ExportConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportConfigType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $columns = [];
        foreach (ExportConfig::getColumnsLabels() as $value => $label) {
            $columns[$label->trans($this->translator)] = $value;
        }

        $builder
            ->add('format', Type\ChoiceType::class, [
                'label'                     => t('field.format', [], 'EkynaUi'),
                'choices'                   => array_flip(ExportConfig::getFormatLabels()),
                'choice_translation_domain' => false,
            ])
            ->add('columns', Type\ChoiceType::class, [
                'label'                     => t('field.columns', [], 'EkynaUi'),
                'choices'                   => $columns,
                'choice_translation_domain' => false,
                'multiple'                  => true,
                'expanded'                  => true,
            ])
            ->add('brands', BrandChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('addInvisible', Type\CheckboxType::class, [
                'label'    => t('export.filter.add_invisible', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('addQuoteOnly', Type\CheckboxType::class, [
                'label'    => t('export.filter.add_quote_only', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('addEndOfLife', Type\CheckboxType::class, [
                'label'    => t('export.filter.add_end_of_life', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('context', ContextType::class, [
                'label' => false,
            ])
            ->add('validUntil', DateTimeType::class, [
                'label' => t('export.column.valid_until', [], 'EkynaProduct'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', ExportConfig::class);
    }
}
