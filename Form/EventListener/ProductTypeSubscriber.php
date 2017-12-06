<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Form\StockSubjectFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\ProductFormBuilder;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ProductTypeSubscriber
 * @package Ekyna\Bundle\ProductBundle\Form\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypeSubscriber implements EventSubscriberInterface
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
     * Constructor.
     *
     * @param ProductFormBuilder      $productBuilder
     * @param StockSubjectFormBuilder $stockBuilder
     */
    public function __construct(ProductFormBuilder $productBuilder, StockSubjectFormBuilder $stockBuilder)
    {
        $this->productBuilder = $productBuilder;
        $this->stockBuilder = $stockBuilder;
    }

    /**
     * Returns the product form builder.
     *
     * @return ProductFormBuilder
     */
    protected function getProductBuilder()
    {
        return $this->productBuilder;
    }

    /**
     * Returns the stock form builder.
     *
     * @return StockSubjectFormBuilder
     */
    protected function getStockBuilder()
    {
        return $this->stockBuilder;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $this->productBuilder->initialize($product = $event->getData(), $event->getForm());
        $this->stockBuilder->initialize($event->getForm());

        $type = $product->getType();
        if (!ProductTypes::isValid($type)) {
            throw new \RuntimeException('Product type not set or invalid.');
        }

        switch ($type) {
            case ProductTypes::TYPE_SIMPLE:
                $this->buildSimpleProductForm();
                break;
            case ProductTypes::TYPE_VARIABLE:
                $this->buildVariableProductForm();
                break;
            case ProductTypes::TYPE_VARIANT:
                $this->buildVariantProductForm();
                break;
            case ProductTypes::TYPE_BUNDLE:
                $this->buildBundleProductForm();
                break;
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->buildConfigurableProductForm();
                break;
            default:
                throw new \InvalidArgumentException('Unexpected product type.');
        }
    }


    /**
     * Builds the simple product form.
     */
    protected function buildSimpleProductForm()
    {
        $this->productBuilder
            ->addDesignationField()
            ->addBrandField()
            ->addVisibleField()
            ->addCategoriesField()
            ->addCustomerGroupsField()
            ->addReleasedAtField()
            ->addReferenceField()
            ->addWeightField()
            ->addTagsField()
            ->addReferencesField()
            ->addTranslationsField()
            ->addMediasField()
            ->addNetPriceField()
            ->addTaxGroupField()
            ->addAdjustmentsField()
            ->addOptionGroupsField()
            ->addSeoField();

        $this->stockBuilder
            ->addStockMode()
            ->addGeocodeField()
            ->addStockFloor()
            ->addReplenishmentTime()
            ->addMinimumOrderQuantity()
            ->addQuoteOnlyField()
            ->addEndOfLifeField();
    }

    /**
     * Builds the variable product form.
     */
    protected function buildVariableProductForm()
    {
        $this->productBuilder
            // General
            ->addDesignationField()
            ->addBrandField()
            ->addVisibleField()
            ->addCategoriesField()
            ->addReferenceField()
            ->addWeightField(['disabled' => true])
            ->addTranslationsField()
            ->addAttributeSetField()
            ->addMediasField()
            // Pricing
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField()
            ->addOptionGroupsField()
            // Seo
            ->addSeoField();

        $this->stockBuilder
            ->addStockMode(['disabled' => true])
            ->addGeocodeField(['disabled' => true])
            ->addStockFloor(['disabled' => true])
            ->addReplenishmentTime(['disabled' => true])
            ->addMinimumOrderQuantity(['disabled' => true])
            ->addQuoteOnlyField()
            ->addEndOfLifeField();
    }

    /**
     * Builds the variant product form.
     */
    protected function buildVariantProductForm()
    {
        $this->productBuilder
            // General
            ->addDesignationField([
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
                ],
            ])
            ->addVisibleField()
            ->addCustomerGroupsField()
            ->addReleasedAtField()
            ->addReferenceField()
            ->addWeightField()
            ->addTagsField()
            ->addTranslationsField([
                'required'     => false,
                'form_options' => [
                    'variant_mode' => true,
                ],
            ])
            ->addReferencesField()
            ->addVariableField()
            ->addAttributesField()
            ->addMediasField()
            // Pricing
            ->addNetPriceField()
            ->addTaxGroupField([
                'allow_new' => false,
                'required'  => false,
                'disabled'  => true,
            ])
            ->addAdjustmentsField()
            ->addOptionGroupsField();

        $this->stockBuilder
            ->addStockMode()
            ->addGeocodeField()
            ->addStockFloor()
            ->addReplenishmentTime()
            ->addMinimumOrderQuantity()
            ->addQuoteOnlyField()
            ->addEndOfLifeField();
    }

    /**
     * Builds the bundle product form.
     */
    protected function buildBundleProductForm()
    {
        $this->productBuilder
            // General
            ->addDesignationField()
            ->addBrandField()
            ->addVisibleField()
            ->addCategoriesField()
            ->addReferenceField()
            ->addWeightField(['disabled' => true])
            ->addTranslationsField()
            ->addMediasField()
            // Pricing
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField()
            ->addBundleSlotsField()
            ->addOptionGroupsField()
            // Seo
            ->addSeoField();

        $this->stockBuilder
            ->addStockMode(['disabled' => true])
            ->addGeocodeField(['disabled' => true])
            ->addStockFloor(['disabled' => true])
            ->addReplenishmentTime(['disabled' => true])
            ->addMinimumOrderQuantity(['disabled' => true])
            ->addQuoteOnlyField(['disabled' => true])
            ->addEndOfLifeField(['disabled' => true]);
    }

    /**
     * Builds the configurable product form.
     */
    protected function buildConfigurableProductForm()
    {
        $this->productBuilder
            // General
            ->addDesignationField()
            ->addBrandField()
            ->addVisibleField()
            ->addCategoriesField()
            ->addReferenceField()
            ->addWeightField(['disabled' => true])
            ->addTranslationsField()
            ->addMediasField()
            // Pricing
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField()
            ->addBundleSlotsField(['configurable' => true])
            ->addOptionGroupsField()
            // Seo
            ->addSeoField();

        $this->stockBuilder
            ->addStockMode(['disabled' => true])
            ->addGeocodeField(['disabled' => true])
            ->addStockFloor(['disabled' => true])
            ->addReplenishmentTime(['disabled' => true])
            ->addMinimumOrderQuantity(['disabled' => true])
            ->addQuoteOnlyField(['disabled' => true])
            ->addEndOfLifeField(['disabled' => true]);
    }

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
        ];
    }
}
