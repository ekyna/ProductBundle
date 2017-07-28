<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResupplyOrderType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Inventory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyOrderType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['full_name'] = 'supplierOrder';
        $view->vars['supplierOrder'] = $options['supplier_order'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('supplier_order', null)
            ->setDefault('value', function (Options $options) {
                /** @var SupplierOrderInterface $supplierOrder */
                if (null !== $supplierOrder = $options['supplier_order']) {
                    return $supplierOrder->getId();
                }

                return null;
            })
            ->setAllowedTypes('supplier_order', ['null', SupplierOrderInterface::class]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_inventory_resupply_order';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return RadioType::class;
    }
}
