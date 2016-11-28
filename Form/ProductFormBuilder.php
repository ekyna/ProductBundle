<?php

namespace Ekyna\Bundle\ProductBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\CommerceBundle\Form\Type\TaxGroupChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaCollectionType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Form\Type as PR;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormInterface;

/**
 * Class ProductFormBuilder
 * @package Ekyna\Bundle\ProductBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductFormBuilder
{
    /**
     * @var string
     */
    private $productClass;

    /**
     * @var string
     */
    private $mediaClass;

    /**
     * @var string
     */
    private $attributeSetClass;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var ProductInterface
     */
    private $product;


    /**
     * Constructor.
     *
     * @param string $productClass
     * @param string $mediaClass
     * @param string $attributeSetClass
     */
    public function __construct($productClass, $mediaClass, $attributeSetClass)
    {
        $this->productClass = $productClass;
        $this->mediaClass = $mediaClass;
        $this->attributeSetClass = $attributeSetClass;
    }

    /**
     * Initializes the builder.
     *
     * @param ProductInterface $product
     * @param FormInterface    $form
     */
    public function initialize(ProductInterface $product, FormInterface $form)
    {
        $this->product = $product;
        $this->form = $form;
    }

    /**
     * Returns the form.
     *
     * @return FormInterface
     */
    protected function getForm()
    {
        return $this->form;
    }

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    protected function getProduct()
    {
        return $this->product;
    }

    /**
     * Adds the attribute set field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addAttributeSetField(array $options = [])
    {
        ProductTypes::assertVariable($this->product);

        $options = array_replace([
            'label'     => 'ekyna_product.attribute_set.label.singular',
            'class'     => $this->attributeSetClass,
            'allow_new' => true,
        ], $options);

        $this->form->add('attributeSet', ResourceType::class, $options);

        return $this;
    }

    /**
     * Adds the attributes field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addAttributesField(array $options = [])
    {
        ProductTypes::assertVariant($this->product);

        $attributeSet = $this->product->getParent()->getAttributeSet();

        $options = array_replace([
            'label'         => 'ekyna_product.attribute.label.plural',
            'attribute_set' => $attributeSet,
            'required'      => $attributeSet->hasRequiredSlot(),
        ], $options);

        $this->form->add('attributes', PR\ProductAttributesType::class, $options);

        return $this;
    }

    /**
     * Adds the brand field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addBrandField(array $options = [])
    {
        $options = array_replace([
            'allow_new' => true,
            'required'  => true,
        ], $options);

        $this->form->add('brand', PR\BrandChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the bundle slots field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addBundleSlotsField(array $options = [])
    {
        $options = array_replace([
            'configurable' => false,
        ], $options);

        $this->form->add('bundleSlots', PR\BundleSlotsType::class, $options);

        return $this;
    }

    /**
     * Adds the categories field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addCategoriesField(array $options = [])
    {
        $options = array_replace([
            'label'     => 'ekyna_product.category.label.plural',
            'multiple'  => true,
            'allow_new' => true,
            'required'  => true,
        ], $options);

        $this->form->add('categories', PR\CategoryChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the designation field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addDesignationField(array $options = [])
    {
        $options = array_replace([
            'label' => 'ekyna_core.field.designation',
        ], $options);

        $this->form->add('designation', SF\TextType::class, $options);

        return $this;
    }

    /**
     * Adds the medias field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addMediasField(array $options = [])
    {
        $options = array_replace([
            'label'       => 'ekyna_core.field.medias',
            'media_class' => $this->mediaClass,
            'types'       => [MediaTypes::IMAGE, MediaTypes::VIDEO, MediaTypes::FILE],
            'required'    => false,
        ], $options);

        $this->form->add('medias', MediaCollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the net price field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addNetPriceField(array $options = [])
    {
        $options = array_replace([
            'label' => 'ekyna_product.product.field.net_price',
            'scale' => 5,
            'attr'  => [
                'input_group' => ['append' => '€'],
            ],
        ], $options);

        $this->form->add('netPrice', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the option groups field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addOptionGroupsField(array $options = [])
    {
        $options = array_replace([
            'label'           => 'ekyna_product.option_group.label.plural',
            'prototype_name'  => '__option_group__',
            'sub_widget_col'  => 11,
            'button_col'      => 1,
            'allow_sort'      => true,
            'entry_type'      => PR\OptionGroupType::class,
            'add_button_text' => 'ekyna_product.option_group.button.add',
            'required'        => false,
        ], $options);

        $this->form->add('optionGroups', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the reference field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addReferenceField(array $options = [])
    {
        $options = array_replace([
            'label' => 'ekyna_core.field.reference',
        ], $options);

        $this->form->add('reference', SF\TextType::class, $options);

        return $this;
    }

    /**
     * Adds the references field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addReferencesField(array $options = [])
    {
        $options = array_replace([
            'label'      => 'ekyna_product.product_reference.label.plural',
            'entry_type' => PR\ProductReferenceType::class,
            'required'   => false,
        ], $options);

        $this->form->add('references', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the seo field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addSeoField(array $options = [])
    {
        $options = array_replace([
            'label'    => false,
            'required' => $this->product->getType() != ProductTypes::TYPE_VARIANT,
        ], $options);

        $this->form->add('seo', SeoType::class, $options);

        return $this;
    }

    /**
     * Adds the tax group field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addTaxGroupField(array $options = [])
    {
        $options = array_replace([
            'allow_new' => true,
        ], $options);

        $this->form->add('taxGroup', TaxGroupChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the translations field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addTranslationsField(array $options = [])
    {
        $options = array_replace([
            'form_type'      => PR\ProductTranslationType::class,
            'label'          => false,
            'error_bubbling' => false,
            'attr'           => [
                'label_col'  => 0,
                'widget_col' => 12,
            ],
        ], $options);

        $this->form->add('translations', TranslationsFormsType::class, $options);

        return $this;
    }

    /**
     * Adds the variable field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addVariableField(array $options = [])
    {
        ProductTypes::assertVariant($this->product);

        $options = array_replace([
            'label'         => 'ekyna_product.product.field.parent',
            'property_path' => 'parent',
            'class'         => $this->productClass,
            'required'      => false,
            'disabled'      => true,
        ], $options);

        $this->form->add('variable', ResourceType::class, $options);

        return $this;
    }

    /**
     * Adds the weight field.
     *
     * @param array $options
     *
     * @return ProductFormBuilder
     */
    public function addWeightField(array $options = [])
    {
        $options = array_replace([
            'label' => 'ekyna_core.field.weight',
            'scale' => 3,
            'attr'  => [
                'placeholder' => 'ekyna_core.field.weight',
                'input_group' => ['append' => 'kg'],
            ],
        ], $options);

        $this->form->add('weight', SF\NumberType::class, $options);

        return $this;
    }
}
