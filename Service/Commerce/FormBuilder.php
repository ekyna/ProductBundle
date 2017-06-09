<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Form\Type as Pr;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Form\Extension\Core\Type as Sf;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormBuilder
{
    /**
     * @var ProductProvider
     */
    private $provider;


    /**
     * Constructor.
     *
     * @param ProductProvider $provider
     */
    public function __construct(ProductProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Builds the sale item form.
     *
     * @param FormInterface     $form
     * @param SaleItemInterface $item
     */
    public function buildItemForm(FormInterface $form, SaleItemInterface $item)
    {
        /** @var Model\ProductInterface $product */
        $product = $this->provider->resolve($item);

        $this->buildProductForm($form, $product);

        // Quantity
        $form->add('quantity', Sf\IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr'  => [
                'class' => 'sale-item-quantity',
                'min'   => 1,
            ],
        ]);
    }

    /**
     * Builds the bundle choice form.
     *
     * @param FormInterface               $form
     * @param Model\BundleChoiceInterface $bundleChoice
     */
    public function buildBundleChoiceForm(FormInterface $form, Model\BundleChoiceInterface $bundleChoice)
    {
        $this->buildProductForm($form, $bundleChoice->getProduct());

        // Quantity
        $form->add('quantity', Sf\IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr'  => [
                'class' => 'sale-item-quantity',
                'min'   => $bundleChoice->getMinQuantity(),
                'max'   => $bundleChoice->getMaxQuantity(),
            ],
        ]);
    }

    /**
     * Builds the product form.
     *
     * @param FormInterface          $form
     * @param Model\ProductInterface $product
     */
    private function buildProductForm(FormInterface $form, Model\ProductInterface $product)
    {
        $repository = $this->provider->getProductRepository();

        // Variable : add variant choice form
        if ($product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
            $repository->loadVariants($product);

            $form->add('variant', Pr\SaleItem\VariantChoiceType::class, [
                'variable' => $product,
            ]);

            // Configurable : add configuration form
        } elseif ($product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
            $repository->loadConfigurableSlots($product);

            foreach ($product->getBundleSlots() as $slot) {
                foreach ($slot->getChoices() as $choice) {
                    $repository->loadMedias($choice->getProduct());
                }
            }

            $form->add('configuration', Pr\SaleItem\ConfigurableSlotsType::class);
        }

        $repository->loadOptions($product);
        $form->add('options', Pr\SaleItem\OptionGroupsType::class);
    }
}
