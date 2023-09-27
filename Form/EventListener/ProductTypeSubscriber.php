<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Form\StockSubjectFormBuilder;
use Ekyna\Bundle\CommerceBundle\Form\SubjectFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\ProductFormBuilder;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductAdjustmentType;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class ProductTypeSubscriber
 * @package Ekyna\Bundle\ProductBundle\Form\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypeSubscriber implements EventSubscriberInterface
{
    private ProductFormBuilder          $productBuilder;
    private SubjectFormBuilder          $subjectBuilder;
    private StockSubjectFormBuilder     $stockBuilder;
    private ResourceRepositoryInterface $attributeSetRepository;

    public function __construct(
        ProductFormBuilder          $productBuilder,
        SubjectFormBuilder          $subjectBuilder,
        StockSubjectFormBuilder     $stockBuilder,
        ResourceRepositoryInterface $attributeSetRepository
    ) {
        $this->productBuilder = $productBuilder;
        $this->subjectBuilder = $subjectBuilder;
        $this->stockBuilder = $stockBuilder;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * Form pre set data event handler.
     */
    public function onPreSetData(FormEvent $event): void
    {
        /** @var ProductInterface $product */
        $product = $event->getData();

        $this->productBuilder->initialize($product, $event->getForm());
        $this->subjectBuilder->initialize($event->getForm());
        $this->stockBuilder->initialize($event->getForm());

        $type = $product->getType();
        if (!ProductTypes::isValid($type)) {
            throw new RuntimeException('Product type not set or invalid.');
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
                throw new InvalidArgumentException('Unexpected product type.');
        }
    }

    /**
     * Form pre submit event handler.
     */
    public function onPreSubmit(FormEvent $event): void
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
            /** @var AttributeSetInterface $attributeSet */
            $attributeSet = $this->attributeSetRepository->find($id);
        }

        $this
            ->getProductBuilder()
            ->addAttributesField($attributeSet);
    }

    /**
     * Builds the simple product form.
     */
    protected function buildSimpleProductForm(): void
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
            ->addReferenceField()
            ->addTagsField()
            ->addMentionsField()
            ->addReferencesField()
            ->addTranslationsField()
            ->addAttributeSetField()
            ->addAttributesField($product->getAttributeSet())
            ->addMediasField()
            ->addNotContractualField()
            ->addInternalManualField()
            ->addExternalManualField()
            ->addCrossSellingsField()
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingGroupField()
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
            ->addReleasedAtField()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField()
            ->addWidthField()
            ->addHeightField()
            ->addDepthField()
            ->addPhysicalField()
            ->addUnitField()
            ->addPackageWeightField()
            ->addPackageWidthField()
            ->addPackageHeightField()
            ->addPackageDepthField();
    }

    /**
     * Builds the variable product form.
     */
    protected function buildVariableProductForm(): void
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
            ->addInternalManualField()
            ->addExternalManualField()
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
            ->addReleasedAtField(['disabled' => true])
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addHeightField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addPhysicalField()
            ->addUnitField()
            ->addPackageWeightField(['disabled' => true])
            ->addPackageWidthField(['disabled' => true])
            ->addPackageHeightField(['disabled' => true])
            ->addPackageDepthField(['disabled' => true]);
    }

    /**
     * Builds the variant product form.
     */
    protected function buildVariantProductForm(): void
    {
        $product = $this->getProductBuilder()->getProduct();

        $this->productBuilder
            // General
            ->addVisibleField()
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
            ->addInternalManualField()
            ->addExternalManualField()
            // Pricing
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingGroupField()
            ->addPricingsField();

        $this->subjectBuilder
            ->addDesignationField([
                'required' => false,
                'attr'     => [
                    'help_text' => t('leave_blank_to_auto_generate', [], 'EkynaProduct'),
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
            ->addReleasedAtField()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField()
            ->addWidthField()
            ->addHeightField()
            ->addDepthField()
            ->addPhysicalField(['disabled' => true])
            ->addUnitField(['disabled' => true])
            ->addPackageWeightField()
            ->addPackageWidthField()
            ->addPackageHeightField()
            ->addPackageDepthField();
    }

    /**
     * Builds the bundle product form.
     */
    protected function buildBundleProductForm(): void
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
            ->addInternalManualField()
            ->addExternalManualField()
            ->addCrossSellingsField()
            // Pricing
            ->addReferencesField()
            ->addBundleSlotsField()
            // TODO (?) ->addComponentsField()
            ->addOptionGroupsField()
            ->addSpecialOffersField()
            ->addPricingGroupField()
            ->addPricingsField()
            // Seo
            ->addSeoField();

        $this->subjectBuilder
            ->addDesignationField()
            ->addNetPriceField(['disabled' => true])
            ->addTaxGroupField();

        $this->stockBuilder
            ->addStockMode(['disabled' => true])
            ->addGeocodeField()
            ->addStockFloor(['disabled' => true])
            ->addReplenishmentTime(['disabled' => true])
            ->addMinimumOrderQuantity(['disabled' => true])
            ->addReleasedAtField()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField()
            ->addWidthField()
            ->addHeightField()
            ->addDepthField()
            ->addPhysicalField(['disabled' => true])
            ->addUnitField(['disabled' => true])
            ->addPackageWeightField()
            ->addPackageWidthField()
            ->addPackageHeightField()
            ->addPackageDepthField();
    }

    /**
     * Builds the configurable product form.
     */
    protected function buildConfigurableProductForm(): void
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
            ->addInternalManualField()
            ->addExternalManualField()
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
            ->addReleasedAtField()
            ->addQuoteOnlyField()
            ->addEndOfLifeField()
            ->addWeightField(['disabled' => true])
            ->addWidthField(['disabled' => true])
            ->addHeightField(['disabled' => true])
            ->addDepthField(['disabled' => true])
            ->addPhysicalField(['disabled' => true])
            ->addUnitField(['disabled' => true])
            ->addPackageWeightField(['disabled' => true])
            ->addPackageWidthField(['disabled' => true])
            ->addPackageHeightField(['disabled' => true])
            ->addPackageDepthField(['disabled' => true]);
    }

    protected function getProductBuilder(): ProductFormBuilder
    {
        return $this->productBuilder;
    }

    protected function getStockBuilder(): StockSubjectFormBuilder
    {
        return $this->stockBuilder;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData', 0],
            FormEvents::PRE_SUBMIT   => ['onPreSubmit', 0],
        ];
    }
}
