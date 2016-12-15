<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Form\Type as Pr;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->productRepository = $repository;
    }

    /**
     * Build the product choice form.
     *
     * @param FormInterface $form
     */
    public function buildChoiceForm(FormInterface $form)
    {
        $form->add('subject', ResourceSearchType::class, [
            'class'    => $this->productRepository->getClassName(),
            'required' => false,
        ]);
    }

    /**
     * Build the product item form.
     *
     * @param FormInterface     $form
     * @param SaleItemInterface $item
     */
    public function buildItemForm(FormInterface $form, SaleItemInterface $item)
    {
        /** @var ProductInterface $product */
        $product = $item->getSubject();

        // Variant : fallback to parent (Variable)
        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            $product = $product->getParent();
        }

        // TODO load options ?
        // $this->productRepository->loadOptions($product);

        // Variable : add variant choice form
        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            $this->productRepository->loadVariants($product);

            $form->add('variant', Pr\VariantChoiceType::class, [
                'variable' => $product,
            ]);

            // Configurable : add configuration form
        } elseif ($product->getType() === ProductTypes::TYPE_CONFIGURABLE) {
            $this->productRepository->loadConfigurableSlots($product);

            foreach ($product->getBundleSlots() as $slot) {
                foreach ($slot->getChoices() as $choice) {
                    $this->productRepository->loadMedias($choice->getProduct());
                }
            }

            $form->add('configuration', Pr\ConfigurableSlotsType::class, [
                'bundle_slots' => $product->getBundleSlots()->toArray(),
                'item'         => $item,
            ]);
        }

        // Quantity
        $form->add('quantity', Sf\IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr'  => [
                'min' => 1,
            ],
        ]);
    }
}
