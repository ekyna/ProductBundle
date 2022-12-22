<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\CommerceBundle\Form\FormHelper;
use Ekyna\Bundle\CommerceBundle\Model\StockAdjustmentReasons;
use Ekyna\Bundle\ProductBundle\Model\BundleStockAdjustment;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BundleStockAdjustmentType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleStockAdjustmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var BundleStockAdjustment $adjustment */
            $adjustment = $event->getData();

            FormHelper::addQuantityType($event->getForm(), $adjustment->bundle->getUnit());
        });

        $builder
            ->add('reason', ConstantChoiceType::class, [
                'label'       => t('stock_adjustment.field.reason', [], 'EkynaCommerce'),
                'placeholder' => t('value.choose', [], 'EkynaUi'),
                'class'       => StockAdjustmentReasons::class,
            ])
            ->add('note', TextType::class, [
                'label'    => t('field.comment', [], 'EkynaUi'),
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', BundleStockAdjustment::class);
    }
}
