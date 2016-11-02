<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Form\Type\ConfigurableSlotsType;
use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FormBuilder
{
    /**
     * @var string
     */
    private $productClass;


    /**
     * Constructor.
     *
     * @param string $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * Build the product choice form.
     *
     * @param FormInterface $form
     */
    public function buildChoiceForm(FormInterface $form)
    {
        $form->add('subject', EntitySearchType::class, [
            'label'           => 'ekyna_product.product.label.singular',
            'class'           => $this->productClass,
            'search_route'    => 'ekyna_product_product_admin_search',
            'find_route'      => 'ekyna_product_product_admin_find',
            'allow_clear'     => false,
            'format_function' =>
                "if(!data.id)return 'Rechercher';" .
                "return $('<span>'+data.designation+'</span>');",
            'required'        => false,
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
        $form->add('quantity', Type\IntegerType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr' => [
                'min' => 1,
            ]
        ]);

        /** @var ProductInterface $product */
        $product = $item->getSubject();

        if ($product->getType() === ProductTypes::TYPE_CONFIGURABLE) {
            $form->add('configuration', ConfigurableSlotsType::class, [
                'bundle_slots' => $product->getBundleSlots()->toArray(),
                'item' => $item,
            ]);
        }
    }
}
