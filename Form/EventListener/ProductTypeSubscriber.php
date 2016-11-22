<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener;

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
     * @var string
     */
    private $builder;


    /**
     * Constructor.
     *
     * @param ProductFormBuilder $builder
     */
    public function __construct(ProductFormBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Returns the product form builder.
     *
     * @return ProductFormBuilder|string
     */
    protected function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $this->builder->initialize($product = $event->getData(), $event->getForm());

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
        $this->builder
            ->addDesignationField()
            ->addBrandField()
            ->addCategoryField()
            ->addReferenceField()
            ->addWeightField()
            ->addReferencesField()
            ->addTranslationsField()
            ->addMediasField()
            ->addNetPriceField()
            ->addTaxGroupField()
            ->addOptionGroupsField()
            ->addSeoField();
    }

    /**
     * Builds the variable product form.
     */
    protected function buildVariableProductForm()
    {
        $this->builder
            // General
            ->addDesignationField()
            ->addBrandField()
            ->addCategoryField()
            ->addReferenceField([
                'required' => false,
                'disabled' => true,
            ])
            ->addWeightField([
                'required' => false,
                'disabled' => true,
            ])
            ->addTranslationsField()
            ->addAttributeSetField()
            ->addMediasField()

            // Pricing
            ->addNetPriceField([
                'required' => false,
                'disabled' => true,
            ])
            ->addTaxGroupField()

            // Seo
            ->addSeoField();
    }

    /**
     * Builds the variant product form.
     */
    protected function buildVariantProductForm()
    {
        $this->builder
            // General
            ->addDesignationField([
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.variant.help.leave_blank_to_auto_generate',
                ],
            ])
            ->addReferenceField()
            ->addWeightField()
            ->addReferencesField()
            ->addVariableField()
            ->addAttributesField()
            ->addMediasField()

            // Pricing
            ->addNetPriceField()
            ->addTaxGroupField([
                'allow_new' => false,
                'required' => false,
                'disabled' => true,
            ])
            ->addOptionGroupsField();
    }

    /**
     * Builds the bundle product form.
     */
    protected function buildBundleProductForm()
    {
        $this->builder
            // General
            ->addDesignationField()
            ->addBrandField()
            ->addCategoryField()
            ->addReferenceField()
            ->addWeightField([
                'required' => false,
                'disabled' => true,
            ])
            ->addTranslationsField()
            ->addMediasField()

            // Pricing
            ->addNetPriceField([
                'required' => false,
                'disabled' => true,
            ])
            ->addTaxGroupField([
                'allow_new' => false,
                'required' => false,
                'disabled' => true,
            ])
            ->addBundleSlotsField()

            // Seo
            ->addSeoField();
    }

    /**
     * Builds the configurable product form.
     */
    protected function buildConfigurableProductForm()
    {
        $this->builder
            // General
            ->addDesignationField()
            ->addBrandField()
            ->addCategoryField()
            ->addReferenceField()
            ->addWeightField([
                'required' => false,
                'disabled' => true,
            ])
            ->addTranslationsField()
            ->addMediasField()

            // Pricing
            ->addNetPriceField([
                'required' => false,
                'disabled' => true,
            ])
            ->addTaxGroupField([
                'allow_new' => false,
                'required' => false,
                'disabled' => true,
            ])
            ->addBundleSlotsField([
                'configurable' => true,
            ])

            // Seo
            ->addSeoField();
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
