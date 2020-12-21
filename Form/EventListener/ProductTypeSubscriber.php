<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Form\StockSubjectFormBuilder;
use Ekyna\Bundle\CommerceBundle\Form\SubjectFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\ProductFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductAdjustmentType;
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
     * @var SubjectFormBuilder
     */
    private $subjectBuilder;

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
     * @param SubjectFormBuilder          $subjectBuilder
     * @param StockSubjectFormBuilder     $stockBuilder
     * @param ResourceRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        ProductFormBuilder $productBuilder,
        SubjectFormBuilder $subjectBuilder,
        StockSubjectFormBuilder $stockBuilder,
        ResourceRepositoryInterface $attributeSetRepository
    ) {
        $this->productBuilder = $productBuilder;
        $this->subjectBuilder = $subjectBuilder;
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
        $this->subjectBuilder->initialize($event->getForm());
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
            /** @var \Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface $attributeSet */
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
            ->addBrandField()
            ->addBrandNamingField()
            ->addVisibleField()
            ->addVisibilityField()
            ->addBestSellerField()
            ->addCrossSellingField()
            ->addCategoriesField()
            ->addReleasedAtField()
            ->addReferenceField()
            ->addTagsField()
            ->addMentionsField()
            ->addReferencesField()
            ->addTranslationsField()
            ->addAttributeSetField()
            ->addAttributesField($product->getAttributeSet())
            ->addMediasField()
            ->addNotContractualField()
            ->addCrossSellingsField()
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingsField()
            ->addSeoField();

        $this->subjectBuilder
            ->addDesignationField()
            ->addCustomerGroupsField()
            ->addNetPriceField()
            ->addTaxGroupField()
            ->addAdjustmentsField([
                'prototype_name' => '__product_adjustment__',
                'entry_type'     => ProductAdjustmentType::class,
            ]);

        $this->stockBuilder
            ->addStockMode()
            ->addGeocodeField()
            ->addStockFloor()
            ->addReplenishmentTime()
            ->addMinimumOrderQuantity()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField()
            ->addWidthField()
            ->addHeightField()
            ->addDepthField()
            ->addUnitField()
            ->addPackageWeightField()
            ->addPackageWidthField()
            ->addPackageHeightField()
            ->addPackageDepthField();
    }


    /**
     * Builds the variable product form.
     */
    protected function buildVariableProductForm()
    {
        $this->productBuilder
            // General
            ->addBrandField()
            ->addBrandNamingField()
            ->addVisibleField()
            ->addVisibilityField()
            ->addBestSellerField()
            ->addCrossSellingField()
            ->addCategoriesField()
            ->addReferenceField()
            ->addMentionsField()
            ->addTranslationsField()
            ->addAttributeSetField()
            ->addMediasField()
            ->addNotContractualField()
            ->addCrossSellingsField()
            // Pricing
            ->addComponentsField()
            ->addOptionGroupsField()
            // Seo
            ->addSeoField();

        $this->subjectBuilder
            ->addDesignationField()
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField();

        $this->stockBuilder
            ->addStockMode(['disabled' => true])
            ->addGeocodeField(['disabled' => true])
            ->addStockFloor(['disabled' => true])
            ->addReplenishmentTime(['disabled' => true])
            ->addMinimumOrderQuantity(['disabled' => true])
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addHeightField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addUnitField()
            ->addPackageWeightField(['disabled' => true])
            ->addPackageWidthField(['disabled' => true])
            ->addPackageHeightField(['disabled' => true])
            ->addPackageDepthField(['disabled' => true]);
    }

    /**
     * Builds the variant product form.
     */
    protected function buildVariantProductForm()
    {
        $product = $this->getProductBuilder()->getProduct();

        $this->productBuilder
            // General
            ->addVisibleField()
            ->addReleasedAtField()
            ->addReferenceField()
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
            ->addNotContractualField()
            // Pricing
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingsField();

        $this->subjectBuilder
            ->addDesignationField([
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
                ],
            ])
            ->addCustomerGroupsField()
            ->addNetPriceField()
            ->addTaxGroupField([
                'allow_new' => false,
                'required'  => false,
                'disabled'  => true,
            ])
            ->addAdjustmentsField([
                'prototype_name' => '__product_adjustment__',
                'entry_type'     => ProductAdjustmentType::class,
            ]);

        $this->stockBuilder
            ->addStockMode()
            ->addGeocodeField()
            ->addStockFloor()
            ->addReplenishmentTime()
            ->addMinimumOrderQuantity()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField()
            ->addWidthField()
            ->addHeightField()
            ->addDepthField()
            ->addUnitField(['disabled' => true])
            ->addPackageWeightField()
            ->addPackageWidthField()
            ->addPackageHeightField()
            ->addPackageDepthField();
    }

    /**
     * Builds the bundle product form.
     */
    protected function buildBundleProductForm()
    {
        $this->productBuilder
            // General
            ->addBrandField()
            ->addBrandNamingField()
            ->addVisibleField()
            ->addVisibilityField()
            ->addBestSellerField()
            ->addCrossSellingField()
            ->addCategoriesField()
            ->addReferenceField()
            ->addTranslationsField()
            ->addMediasField()
            ->addNotContractualField()
            ->addCrossSellingsField()
            // Pricing
            ->addReferencesField()
            ->addBundleSlotsField()
            // TODO (?) ->addComponentsField()
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingsField()
            // Seo
            ->addSeoField();

        $this->subjectBuilder
            ->addDesignationField()
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField();

        $this->stockBuilder
            ->addStockMode(['disabled' => true])
            ->addGeocodeField(['disabled' => true])
            ->addStockFloor(['disabled' => true])
            ->addReplenishmentTime(['disabled' => true])
            ->addMinimumOrderQuantity()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addHeightField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addUnitField(['disabled' => true])
            ->addPackageWeightField(['disabled' => true])
            ->addPackageWidthField(['disabled' => true])
            ->addPackageHeightField(['disabled' => true])
            ->addPackageDepthField(['disabled' => true]);
    }

    /**
     * Builds the configurable product form.
     */
    protected function buildConfigurableProductForm()
    {
        $this->productBuilder
            // General
            ->addBrandField()
            ->addBrandNamingField()
            ->addVisibleField()
            ->addVisibilityField()
            ->addBestSellerField()
            ->addCrossSellingField()
            ->addCategoriesField()
            ->addReferenceField()
            ->addTranslationsField()
            ->addMediasField()
            ->addNotContractualField()
            ->addCrossSellingsField()
            // Pricing
            ->addBundleSlotsField(['configurable' => true])
            ->addOptionGroupsField()
            // Seo
            ->addSeoField();

        $this->subjectBuilder
            ->addDesignationField()
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField();

        $this->stockBuilder
            ->addStockMode(['disabled' => true])
            ->addGeocodeField(['disabled' => true])
            ->addStockFloor(['disabled' => true])
            ->addReplenishmentTime(['disabled' => true])
            ->addMinimumOrderQuantity()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addHeightField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addUnitField(['disabled' => true])
            ->addPackageWeightField(['disabled' => true])
            ->addPackageWidthField(['disabled' => true])
            ->addPackageHeightField(['disabled' => true])
            ->addPackageDepthField(['disabled' => true]);
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
     * @inheritDoc
     */
    static public function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
            FormEvents::PRE_SUBMIT   => ['onPreSubmit', 0],
        ];
    }
}
