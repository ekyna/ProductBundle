<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\StockView;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResupplyOrdersType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\StockView
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyOrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<SupplierOrderInterface> $supplierOrders */
        $supplierOrders = $options['supplier_orders'];

        foreach ($supplierOrders as $supplierOrder) {
            $builder->add('so_' . $supplierOrder->getId(), ResupplyOrderType::class, [
                'supplier_order' => $supplierOrder,
                'required'       => true,
            ]);
        }

        $builder->add('none', ResupplyOrderType::class, [
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'supplier_orders' => [],
            ])
            ->setAllowedTypes('supplier_orders', 'array')
            ->setAllowedValues('supplier_orders', function ($value) {
                foreach ($value as $sp) {
                    if (!$sp instanceof SupplierOrderInterface) {
                        throw new InvalidOptionsException('Expected array of ' . SupplierOrderInterface::class);
                    }
                }

                return true;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_inventory_resupply_orders';
    }
}
