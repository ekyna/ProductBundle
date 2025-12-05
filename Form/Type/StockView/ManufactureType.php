<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\StockView;


use Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture\BillOfMaterialsChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\WarehouseChoiceType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function is_null;
use function Symfony\Component\Translation\t;

/**
 * Class ManufactureType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\StockView
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ManufactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => t('field.quantity', [], 'EkynaUi'),
                'attr'  => [
                    'min' => 1,
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $order = $event->getData();

            if (!$order instanceof ProductionOrderInterface) {
                throw new UnexpectedTypeException($order, ProductionOrderInterface::class);
            }

            $event
                ->getForm()
                ->add('bom', BillOfMaterialsChoiceType::class, [
                    'disabled' => !is_null($order->getBom()),
                ])
                ->add('warehouse', WarehouseChoiceType::class, [
                    'disabled' => !is_null($order->getWarehouse()),
                ]);
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var ProductInterface $product */
        $product = $options['product'];

        $view->vars['product'] = $product;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => ProductionOrderInterface::class,
                'product'    => null,
            ])
            ->setAllowedTypes('product', ProductInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_inventory_manufacture';
    }
}
