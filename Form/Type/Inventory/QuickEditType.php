<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Inventory;

use Ekyna\Bundle\CommerceBundle\Form\StockSubjectFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\ProductFormBuilder;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QuickEditType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Inventory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuickEditType extends AbstractType
{
    /**
     * @var ProductFormBuilder
     */
    private $productBuilder;

    /**
     * @var StockSubjectFormBuilder
     */
    private $stockBuilder;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;


    /**
     * Constructor.
     *
     * @param ProductFormBuilder $productBuilder
     * @param StockSubjectFormBuilder $stockBuilder
     * @param TaxResolverInterface $taxResolver
     */
    public function __construct(
        ProductFormBuilder $productBuilder,
        StockSubjectFormBuilder $stockBuilder,
        TaxResolverInterface $taxResolver
    ) {
        $this->productBuilder = $productBuilder;
        $this->stockBuilder = $stockBuilder;
        $this->taxResolver = $taxResolver;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var ProductInterface $product */
            $product = $event->getData();
            $form = $event->getForm();

            $this->productBuilder->initialize($product, $form);
            $this->stockBuilder->initialize($form);

            $rates = [];
            $taxes = $this->taxResolver->resolveTaxes($product);
            foreach ($taxes as $tax) {
                $rates[] = $tax->getRate() / 100;
            }

            $this->productBuilder
                ->addNetPriceField([
                    'rates' => $rates,
                ])
                ->addWeightField();

            $this->stockBuilder
                ->addGeocodeField()
                ->addStockFloor();
        });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => ProductInterface::class,
            'validation_groups' => function (FormInterface $form) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
                $product = $form->getData();

                if (!strlen($type = $product->getType())) {
                    throw new \RuntimeException('Product type is not set.');
                }

                return ['Default', $product->getType()];
            },
        ]);
    }
}
