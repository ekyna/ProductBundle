<?php

namespace Ekyna\Bundle\ProductBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionsType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\MediaBundle\Form\Type\MediaCollectionType;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Entity\ProductMention;
use Ekyna\Bundle\ProductBundle\Entity\ProductMentionTranslation;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Form\Type as PR;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Features;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormBuilderInterface;
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
    private $mediaClass;

    /**
     * @var FormInterface|FormBuilderInterface
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
     * @param string   $mediaClass
     */
    public function __construct(Features $features, string $mediaClass)
    {
        $this->features   = $features;
        $this->mediaClass = $mediaClass;
    }

    /**
     * Initializes the builder.
     *
     * @param ProductInterface                   $product
     * @param FormInterface|FormBuilderInterface $form
     *
     * @return ProductFormBuilder
     */
    public function initialize(ProductInterface $product, $form)
    {
        if (!($form instanceof FormInterface || $form instanceof FormBuilderInterface)) {
            throw new UnexpectedTypeException($form, [FormInterface::class, FormBuilderInterface::class]);
        }

        $this->product = $product;
        $this->form    = $form;

        return $this;
    }

    /**
     * Returns the form.
     *
     * @return FormInterface|FormBuilderInterface
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
        $attr                = [];
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
     * Adds the brand naming field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addBrandNamingField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_product.product.field.brand_naming',
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('brandNaming', SF\CheckboxType::class, $options);

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
     * Adds the cross sellings field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addCrossSellingsField(array $options = [])
    {
        /*if (!$this->features->isEnabled(Features::CROSS_SELLING)) {
            return $this;
        }*/

        $this->form->add('crossSellings', PR\CrossSelling\CrossSellingsType::class, $options);

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
     * Adds the mentions field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addMentionsField(array $options = [])
    {
        $options = array_replace([
            'mention_class'     => ProductMention::class,
            'translation_class' => ProductMentionTranslation::class,
        ], $options);

        $this->form->add('mentions', MentionsType::class, $options);

        return $this;
    }

    /**
     * Adds the "not contractual" field.
     *
     * @param array $options
     *
     * @return self
     */
    public function addNotContractualField(array $options = [])
    {
        $options = array_replace([
            'label'    => 'ekyna_product.product.field.not_contractual',
            'required' => false,
            'attr'     => [
                'align_with_widget' => true,
            ],
        ], $options);

        $this->form->add('notContractual', SF\CheckboxType::class, $options);

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
            'disabled' => !empty($this->product->getReference()),
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
            'property_path' => 'parent',
            'types'         => [ProductTypes::TYPE_VARIABLE],
            'required'      => false,
            'disabled'      => true,
        ], $options);

        $this->form->add('variable', PR\ProductChoiceType::class, $options);

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
}
