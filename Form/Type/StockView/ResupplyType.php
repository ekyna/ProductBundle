<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\StockView;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

use function Symfony\Component\Translation\t;

/**
 * Class ResupplyType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\StockView
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResupplyType extends AbstractType
{
    private SupplierProductRepositoryInterface $supplierProductRepository;

    public function __construct(SupplierProductRepositoryInterface $supplierProductRepository)
    {
        $this->supplierProductRepository = $supplierProductRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ProductInterface $product */
        $product = $options['product'];

        $supplierProducts = $this->supplierProductRepository->findBySubject($product);

        $builder
            ->add('supplierProduct', ResupplyProductsType::class, [
                'supplier_products' => $supplierProducts,
            ])
            ->add('quantity', NumberType::class, [
                'label'       => t('field.quantity', [], 'EkynaUi'),
                'decimal'     => true,
                'scale'       => 3, // TODO Packaging format
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                ],
                'attr'        => [
                    'class' => 'resupply-quantity',
                ],
            ])
            ->add('netPrice', NumberType::class, [
                'label'       => t('field.buy_net_price', [], 'EkynaCommerce'),
                'decimal'     => true,
                'scale'       => 5,
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(0),
                ],
                'attr'        => [
                    'class' => 'resupply-net-price',
                ],
            ])
            ->add('estimatedDateOfArrival', DateTimeType::class, [
                'label'    => t('field.eta', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class' => 'resupply-eda',
                ],
            ]);
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
            ->setDefault('product', null)
            ->setAllowedTypes('product', ProductInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_inventory_resupply';
    }
}
