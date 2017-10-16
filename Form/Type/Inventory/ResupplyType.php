<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ResupplyType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Inventory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyType extends AbstractType
{
    /**
     * @var SupplierProductRepositoryInterface
     */
    private $supplierProductRepository;


    /**
     * Constructor.
     *
     * @param SupplierProductRepositoryInterface $supplierProductRepository
     */
    public function __construct(SupplierProductRepositoryInterface $supplierProductRepository)
    {
        $this->supplierProductRepository = $supplierProductRepository;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ProductInterface $product */
        $product = $options['product'];

        $supplierProducts = $this->supplierProductRepository->findBySubject($product);

        $builder
            ->add('supplierProduct', ResupplyProductsType::class, [
                'supplier_products' => $supplierProducts,
            ])
            ->add('quantity', NumberType::class, [
                'label'       => 'ekyna_core.field.quantity',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('netPrice', NumberType::class, [
                'label'       => 'ekyna_commerce.supplier_product.field.net_price',
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('estimatedDateOfArrival', DateTimeType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.estimated_date_of_arrival',
                'format'   => 'dd/MM/yyyy',
                'required' => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('product', null)
            ->setAllowedTypes('product', ProductInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_inventory_resupply';
    }
}