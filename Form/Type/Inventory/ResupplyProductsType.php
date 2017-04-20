<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResupplyProductsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Inventory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SupplierProductInterface[] $supplierProducts */
        $supplierProducts = $options['supplier_products'];

        foreach ($supplierProducts as $supplierProduct) {
            $builder->add('sp_' . $supplierProduct->getId(), ResupplyProductType::class, [
                'supplier_product' => $supplierProduct,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'supplier_products' => [],
            ])
            ->setAllowedTypes('supplier_products', 'array')
            ->setAllowedValues('supplier_products', function ($value) {
                foreach ($value as $sp) {
                    if (!$sp instanceof SupplierProductInterface) {
                        throw new InvalidOptionsException('Expected array of ' . SupplierProductInterface::class);
                    }
                }

                return true;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_inventory_resupply_products';
    }
}
