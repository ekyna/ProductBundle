<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\StockView;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResupplyOrderType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\StockView
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyOrderType extends AbstractType
{
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['full_name'] = 'supplierOrder';
        $view->vars['supplierOrder'] = $options['supplier_order'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('supplier_order', null)
            ->setDefault('value', function (Options $options) {
                /** @var SupplierOrderInterface $supplierOrder */
                if (null !== $supplierOrder = $options['supplier_order']) {
                    return $supplierOrder->getId();
                }

                return '0';
            })
            ->setAllowedTypes('supplier_order', ['null', SupplierOrderInterface::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_inventory_resupply_order';
    }

    public function getParent(): ?string
    {
        return RadioType::class;
    }
}
