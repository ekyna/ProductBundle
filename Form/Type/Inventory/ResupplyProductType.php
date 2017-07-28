<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

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
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Inventory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyProductType extends AbstractType
{
    /**
     * @var SupplierOrderRepositoryInterface
     */
    private $supplierOrderRepository;


    /**
     * Constructor.
     *
     * @param SupplierOrderRepositoryInterface $supplierOrderRepository
     */
    public function __construct(SupplierOrderRepositoryInterface $supplierOrderRepository)
    {
        $this->supplierOrderRepository = $supplierOrderRepository;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var SupplierProductInterface $supplierProduct */
        $supplierProduct = $options['supplier_product'];

        $supplierOrders = $this->supplierOrderRepository->findNewBySupplier($supplierProduct->getSupplier());

        $builder
            ->add('choice', RadioType::class, [
                'label'    => $supplierProduct->getDesignation(),
                'value'    => $supplierProduct->getId(),
                'required' => true,
            ])
            ->add('supplierOrder', ResupplyOrdersType::class, [
                'supplier_orders' => $supplierOrders,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view['choice']->vars['full_name'] = 'supplierProduct';
        $view->vars['supplierProduct'] = $options['supplier_product'];
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('supplier_product', null)
            ->setAllowedTypes('supplier_product', SupplierProductInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_inventory_resupply_product';
    }
}
