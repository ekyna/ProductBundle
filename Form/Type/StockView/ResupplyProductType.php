<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\StockView;

use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResupplyProductType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\StockView
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyProductType extends AbstractType
{
    private SupplierOrderRepositoryInterface $supplierOrderRepository;

    public function __construct(SupplierOrderRepositoryInterface $supplierOrderRepository)
    {
        $this->supplierOrderRepository = $supplierOrderRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var SupplierProductInterface $supplierProduct */
        $supplierProduct = $options['supplier_product'];

        $supplierOrders = $this->supplierOrderRepository->findNewBySupplier($supplierProduct->getSupplier());

        $builder
            ->add('choice', RadioType::class, [
                'label'    => $supplierProduct->getDesignation(),
                'value'    => (string)$supplierProduct->getId(),
                'required' => true,
                'attr' => [
                    'data-price' => $supplierProduct->getNetPrice(),
                ],
            ])
            ->add('supplierOrder', ResupplyOrdersType::class, [
                'supplier_orders' => $supplierOrders,
            ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view['choice']->vars['full_name'] = 'supplierProduct';
        $view->vars['supplierProduct'] = $options['supplier_product'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('supplier_product', null)
            ->setAllowedTypes('supplier_product', SupplierProductInterface::class);
    }

    public function getBlockPrefix(): ?string
    {
        return 'ekyna_product_inventory_resupply_product';
    }
}
