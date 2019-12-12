<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\ContextType;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ExportConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportConfigType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('format', Type\ChoiceType::class, [
                'label'   => 'ekyna_core.field.format',
                'choices' => ExportConfig::getFormatChoices(),
            ])
            ->add('columns', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.columns',
                'choices'  => ExportConfig::getColumnsChoices(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('brands', BrandChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('visible', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.visible',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('context', ContextType::class, [
                'label'      => false,
                'admin_mode' => $options['admin_mode'],
            ])
            ->add('validUntil', DateTimeType::class, [
                'label'  => 'ekyna_product.export.column.valid_until',
                'format' => 'dd/MM/yyyy',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ExportConfig::class);
    }
}
