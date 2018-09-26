<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Form\StockSubjectFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\ProductFormBuilder;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
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
     * @var ResourceRepositoryInterface
     */
    private $attributeSetRepository;


    /**
     * Constructor.
     *
     * @param ProductFormBuilder          $productBuilder
     * @param StockSubjectFormBuilder     $stockBuilder
     * @param ResourceRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        ProductFormBuilder $productBuilder,
        StockSubjectFormBuilder $stockBuilder,
        ResourceRepositoryInterface $attributeSetRepository
    ) {
        $this->productBuilder = $productBuilder;
        $this->stockBuilder = $stockBuilder;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var ProductInterface $product */
        $product = $event->getData();

        $this->productBuilder->initialize($product, $event->getForm());
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
     * Form pre submit event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $product = $this->productBuilder->getProduct();

        if ($product->getType() !== ProductTypes::TYPE_SIMPLE) {
            return;
        }

        $data = $event->getData();
        $form = $event->getForm();

        if (!isset($data['attributeSet'])) {
            return;
        }

        $form->remove('attributes');

        $attributeSet = null;
        if (0 < $id = intval($data['attributeSet'])) {
            $attributeSet = $this->attributeSetRepository->find($id);
        }

        $this
            ->getProductBuilder()
            ->addAttributesField($attributeSet);
    }

    /**
     * Builds the simple product form.
     */
    protected function buildSimpleProductForm()
    {
        $product = $this->getProductBuilder()->getProduct();

        $this->productBuilder
            ->addDesignationField()
            ->addBrandField()
            ->addVisibleField()
            ->addCategoriesField()
            ->addCustomerGroupsField()
            ->addReleasedAtField()
            ->addReferenceField()
            ->addWeightField()
            ->addHeightField()
            ->addWidthField()
            ->addDepthField()
            ->addUnitField()
            ->addTagsField()
            ->addReferencesField()
            ->addTranslationsField()
            ->addAttributeSetField()
            ->addAttributesField($product->getAttributeSet())
            ->addMediasField()
            ->addNetPriceField()
            ->addTaxGroupField()
            ->addAdjustmentsField()
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingsField()
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
            ->addHeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addUnitField()
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
        $product = $this->getProductBuilder()->getProduct();

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
            ->addHeightField()
            ->addWidthField()
            ->addDepthField()
            ->addUnitField(['disabled' => true])
            ->addTagsField()
            ->addTranslationsField([
                'required'     => false,
                'form_options' => [
                    'variant_mode' => true,
                ],
            ])
            ->addReferencesField()
            ->addVariableField()
            ->addAttributesField($product->getParent()->getAttributeSet())
            ->addMediasField()
            // Pricing
            ->addNetPriceField()
            ->addTaxGroupField([
                'allow_new' => false,
                'required'  => false,
                'disabled'  => true,
            ])
            ->addAdjustmentsField()
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingsField();

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
            ->addHeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addUnitField(['disabled' => true])
            ->addTranslationsField()
            ->addMediasField()
            // Pricing
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField()
            ->addBundleSlotsField()
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingsField()
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
            ->addHeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addUnitField(['disabled' => true])
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
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
            FormEvents::PRE_SUBMIT   => ['onPreSubmit', 0],
        ];
    }
}
