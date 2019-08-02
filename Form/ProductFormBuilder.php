<?php

namespace Ekyna\Bundle\ProductBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type as CO;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaCollectionType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Form\Type as PR;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\Units;
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
     * @var Features
     */
    private $features;

    /**
     * @var string
     */
    private $productClass;

    /**
     * @var string
     */
    private $mediaClass;

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
     * @param Features $features
     * @param string   $productClass
     * @param string   $mediaClass
     */
    public function __construct(Features $features, string $productClass, string $mediaClass)
    {
        $this->features = $features;
        $this->productClass = $productClass;
        $this->mediaClass = $mediaClass;
    }

    /**
     * Initializes the builder.
     *
     * @param ProductInterface $product
     * @param FormInterface    $form
     *
     * @return ProductFormBuilder
     */
    public function initialize(ProductInterface $product, FormInterface $form)
    {
        $this->product = $product;
        $this->form = $form;

        return $this;
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Adds the adjustments field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addAdjustmentsField(array $options = [])
    {
        $options = array_replace([
            'label'                 => 'ekyna_commerce.adjustment.label.plural',
            'prototype_name'        => '__product_adjustment__',
            'entry_type'            => PR\ProductAdjustmentType::class,
            'add_button_text'       => 'ekyna_commerce.sale.form.add_item_adjustment',
            'delete_button_confirm' => 'ekyna_commerce.sale.form.remove_item_adjustment',
            'attr'                  => ['label_col' => 2, 'widget_col' => 10],
            'modes'                 => [AdjustmentModes::MODE_FLAT],
            'types'                 => [AdjustmentTypes::TYPE_INCLUDED],
            'required'              => false,
        ], $options);

        $this->form->add('adjustments', CO\Common\AdjustmentsType::class, $options);

        return $this;
    }

    /**
     * Adds the attribute set field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addAttributeSetField(array $options = [])
    {
        if (!in_array($this->product->getType(), [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIABLE])) {
            throw new InvalidArgumentException("Expected 'simple' or 'variable' product.");
        }

        $options['required'] = true;
        $options['disabled'] = false;
        $attr = [];
        if ($this->product->getType() === ProductTypes::TYPE_SIMPLE) {
            $options['required'] = false;
            if (null !== $this->product->getAttributeSet()) {
                $options['disabled'] = true;;
            } else {
                $attr['class'] = 'product-attribute-set';
            }
        }

        $options = array_replace([
            'allow_new' => true,
            'attr'      => $attr,
        ], $options);

        $this->form->add('attributeSet', PR\Attribute\AttributeSetChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the attributes field.
     *
     * @param AttributeSetInterface $attributeSet
     * @param array                 $options
     *
     * @return self
     */
    public function addAttributesField(AttributeSetInterface $attributeSet = null, array $options = [])
    {
        ProductTypes::assertChildType($this->product);

        $options = array_replace([
            'label'         => 'ekyna_product.attribute.label.plural',
            'attribute_set' => $attributeSet,
        ], $options);

        $this->form->add('attributes', PR\ProductAttributesType::class, $options);

        return $this;
    }

    /**
     * Adds the brand field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addBrandField(array $options = [])
    {
        $options = array_replace([
            'allow_new' => true,
            'required'  => true,
        ], $options);

        $this->form->add('brand', PR\Brand\BrandChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the bundle slots field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addBundleSlotsField(array $options = [])
    {
        $options = array_replace([
            'configurable' => false,
        ], $options);

        $this->form->add('bundleSlots', PR\Bundle\BundleSlotsType::class, $options);

        return $this;
    }

    /**
     * Adds the categories field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addCategoriesField(array $options = [])
    {
        $options = array_replace([
            'label'     => 'ekyna_product.category.label.plural',
            'multiple'  => true,
            'allow_new' => true,
            'required'  => true,
        ], $options);

        $this->form->add('categories', PR\Category\CategoryChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the components field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addComponentsField(array $options = [])
    {
        if (!$this->features->isEnabled(Features::COMPONENT)) {
            return $this;
        }

        $this->form->add('components', PR\Component\ComponentsType::class, $options);

        return $this;
    }

    /**
     * Adds the customer groups field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addCustomerGroupsField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.customer_group.label.plural',
            'multiple' => true,
            'required' => false,
        ], $options);

        $this->form->add('customerGroups', CO\Customer\CustomerGroupChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the designation field.
     *
     * @param array $options
     *
     * @return self
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
     * @return self
     */
    public function addMediasField(array $options = [])
    {
        $options = array_replace([
            'label'       => 'ekyna_core.field.medias',
            'media_class' => $this->mediaClass,
            'types'       => [
                MediaTypes::IMAGE,
                MediaTypes::SVG,
                MediaTypes::FLASH,
                MediaTypes::VIDEO,
                MediaTypes::FILE,
            ],
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
     * @return self
     */
    public function addNetPriceField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.field.net_price',
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('netPrice', CO\Pricing\PriceType::class, $options);

        return $this;
    }

    /**
     * Adds the option groups field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addOptionGroupsField(array $options = [])
    {
        $options = array_replace([
            'label'           => 'ekyna_product.option_group.label.plural',
            'prototype_name'  => '__option_group__',
            'sub_widget_col'  => 11,
            'button_col'      => 1,
            'entry_type'      => PR\Option\OptionGroupType::class,
            'allow_add'       => true,
            'allow_delete'    => true,
            'allow_sort'      => true,
            'add_button_text' => 'ekyna_product.option_group.button.add',
            'required'        => false,
        ], $options);

        $this->form->add('optionGroups', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the pricings field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addPricingsField(array $options = [])
    {
        if (in_array($this->product->getType(), [ProductTypes::TYPE_VARIABLE, ProductTypes::TYPE_CONFIGURABLE])) {
            throw new InvalidArgumentException("Unexpected product type.");
        }

        $options = array_replace([
            'label'           => 'ekyna_product.pricing.label.plural',
            'entry_type'      => PR\Pricing\PricingType::class,
            'entry_options'   => ['product_mode' => true],
            'prototype_name'  => '__pricing__',
            'allow_add'       => true,
            'allow_delete'    => true,
            'add_button_text' => 'ekyna_product.pricing.button.add',
            'required'        => false,
        ], $options);

        $this->form->add('pricings', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the reference field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addReferenceField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.reference',
            'required' => false,
        ], $options);

        $this->form->add('reference', SF\TextType::class, $options);

        return $this;
    }

    /**
     * Adds the references field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addReferencesField(array $options = [])
    {
        $options = array_replace([
            'label'        => 'ekyna_product.product_reference.label.plural',
            'entry_type'   => PR\ProductReferenceType::class,
            'allow_add'    => true,
            'allow_delete' => true,
            'required'     => false,
        ], $options);

        $this->form->add('references', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the released at field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addReleasedAtField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_product.product.field.released_at',
            'format'   => 'dd/MM/yyyy',
            'required' => false,
        ], $options);

        $this->form->add('releasedAt', SF\DateTimeType::class, $options);

        return $this;
    }

    /**
     * Adds the seo field.
     *
     * @param array $options
     *
     * @return self
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
     * Adds the special offers field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addSpecialOffersField(array $options = [])
    {
        if (in_array($this->product->getType(), [ProductTypes::TYPE_VARIABLE, ProductTypes::TYPE_CONFIGURABLE])) {
            throw new InvalidArgumentException("Unexpected product type.");
        }

        $options = array_replace([
            'label'           => 'ekyna_product.special_offer.label.plural',
            'entry_type'      => PR\SpecialOffer\SpecialOfferType::class,
            'entry_options'   => ['product_mode' => true],
            'allow_add'       => true,
            'allow_delete'    => true,
            'add_button_text' => 'ekyna_product.special_offer.button.add',
            'required'        => false,
        ], $options);

        $this->form->add('specialOffers', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the tags field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addTagsField(array $options = [])
    {
        $options = array_replace([
            'multiple' => true,
            'required' => false,
        ], $options);

        $this->form->add('tags', TagChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the tax group field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addTaxGroupField(array $options = [])
    {
        /*$options = array_replace([
            'allow_new' => true,
        ], $options);*/

        $this->form->add('taxGroup', CO\Pricing\TaxGroupChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the translations field.
     *
     * @param array $options
     *
     * @return self
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
     * @return self
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
     * Adds the visible field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addVisibleField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.visible',
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('visible', SF\CheckboxType::class, $options);

        return $this;
    }

    /**
     * Adds the visibility field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addVisibilityField(array $options = [])
    {
        $options = array_replace([
            'label' => 'ekyna_product.common.visibility',
        ], $options);

        $this->form->add('visibility', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the best seller field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addBestSellerField(array $options = [])
    {
        $options = array_replace([
            'label'   => 'ekyna_product.product.field.best_seller',
            'choices' => HighlightModes::getChoices(),
            'select2' => false,
        ], $options);

        $this->form->add('bestSeller', SF\ChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the cross selling field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addCrossSellingField(array $options = [])
    {
        $options = array_replace([
            'label'   => 'ekyna_product.product.field.cross_selling',
            'choices' => HighlightModes::getChoices(),
            'select2' => false,
        ], $options);

        $this->form->add('crossSelling', SF\ChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the weight field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addWeightField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.weight',
            'scale'    => 3,
            'attr'     => [
                'placeholder' => 'ekyna_core.field.weight',
                'input_group' => ['append' => Units::getSymbol(Units::KILOGRAM)],
            ],
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('weight', SF\NumberType::class, $options);

        return $this;
    }

    /**
     * Adds the height field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addHeightField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.height',
            'attr'     => [
                'placeholder' => 'ekyna_core.field.height',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('height', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the width field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addWidthField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.width',
            'attr'     => [
                'placeholder' => 'ekyna_core.field.width',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('width', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the depth field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addDepthField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_core.field.depth',
            'attr'     => [
                'placeholder' => 'ekyna_core.field.depth',
                'input_group' => ['append' => Units::getSymbol(Units::MILLIMETER)],
            ],
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('depth', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the quantity unit field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addUnitField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_commerce.unit.label',
            'attr'     => [
                'placeholder' => 'ekyna_commerce.unit.label',
            ],
            'required' => !(isset($options['disabled']) && $options['disabled']),
        ], $options);

        $this->form->add('unit', CO\Common\UnitChoiceType::class, $options);

        return $this;
    }
}
