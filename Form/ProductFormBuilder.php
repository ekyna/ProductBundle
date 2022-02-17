<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MentionsType;
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
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

use function array_replace;
use function in_array;
use function Symfony\Component\Translation\t;

/**
 * Class ProductFormBuilder
 * @package Ekyna\Bundle\ProductBundle\Form
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductFormBuilder
{
    private Features $features;
    private string   $mediaClass;

    private ?ProductInterface $product = null;
    /** @var FormInterface|FormBuilderInterface */
    private $form = null;

    public function __construct(Features $features, string $mediaClass)
    {
        $this->features = $features;
        $this->mediaClass = $mediaClass;
    }

    /**
     * Initializes the builder.
     *
     * @param FormInterface|FormBuilderInterface $form
     */
    public function initialize(ProductInterface $product, $form): ProductFormBuilder
    {
        if (!($form instanceof FormInterface || $form instanceof FormBuilderInterface)) {
            throw new UnexpectedTypeException($form, [FormInterface::class, FormBuilderInterface::class]);
        }

        $this->product = $product;
        $this->form = $form;

        return $this;
    }

    /**
     * @return FormInterface|FormBuilderInterface
     */
    protected function getForm()
    {
        return $this->form;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    /**
     * Adds the attribute set field.
     */
    public function addAttributeSetField(array $options = []): ProductFormBuilder
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
                $options['disabled'] = true;
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
     */
    public function addAttributesField(?AttributeSetInterface $attributeSet, array $options = []): ProductFormBuilder
    {
        ProductTypes::assertChildType($this->product);

        $options = array_replace([
            'label'         => t('attribute.label.plural', [], 'EkynaProduct'),
            'attribute_set' => $attributeSet,
        ], $options);

        $this->form->add('attributes', PR\ProductAttributesType::class, $options);

        return $this;
    }

    /**
     * Adds the brand field.
     */
    public function addBrandField(array $options = []): ProductFormBuilder
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
     */
    public function addBrandNamingField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'    => t('product.field.brand_naming', [], 'EkynaProduct'),
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
     */
    public function addBundleSlotsField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'configurable' => false,
        ], $options);

        $this->form->add('bundleSlots', PR\Bundle\BundleSlotsType::class, $options);

        return $this;
    }

    /**
     * Adds the categories field.
     */
    public function addCategoriesField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'resource'  => 'ekyna_product.category',
            'multiple'  => true,
            'allow_new' => true,
            'required'  => true,
        ], $options);

        $this->form->add('categories', ResourceChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the components field.
     */
    public function addComponentsField(array $options = []): ProductFormBuilder
    {
        if (!$this->features->isEnabled(Features::COMPONENT)) {
            return $this;
        }

        $this->form->add('components', PR\Component\ComponentsType::class, $options);

        return $this;
    }

    /**
     * Adds the cross sellings field.
     */
    public function addCrossSellingsField(array $options = []): ProductFormBuilder
    {
        /*if (!$this->features->isEnabled(Features::CROSS_SELLING)) {
            return $this;
        }*/

        $this->form->add('crossSellings', PR\CrossSelling\CrossSellingsType::class, $options);

        return $this;
    }

    /**
     * Adds the medias field.
     */
    public function addMediasField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'       => t('field.medias', [], 'EkynaUi'),
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
     */
    public function addMentionsField(array $options = []): ProductFormBuilder
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
     */
    public function addNotContractualField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'    => t('product.field.not_contractual', [], 'EkynaProduct'),
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
     */
    public function addOptionGroupsField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'           => t('option_group.label.plural', [], 'EkynaProduct'),
            'prototype_name'  => '__option_group__',
            'sub_widget_col'  => 11,
            'button_col'      => 1,
            'entry_type'      => PR\Option\OptionGroupType::class,
            'allow_add'       => true,
            'allow_delete'    => true,
            'allow_sort'      => true,
            'add_button_text' => t('option_group.button.add', [], 'EkynaProduct'),
            'required'        => false,
        ], $options);

        $this->form->add('optionGroups', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the pricings field.
     */
    public function addPricingsField(array $options = []): ProductFormBuilder
    {
        if (in_array($this->product->getType(), [ProductTypes::TYPE_VARIABLE, ProductTypes::TYPE_CONFIGURABLE])) {
            throw new InvalidArgumentException('Unexpected product type.');
        }

        $options = array_replace([
            'label'           => t('pricing.label.plural', [], 'EkynaProduct'),
            'entry_type'      => PR\Pricing\PricingType::class,
            'entry_options'   => ['product_mode' => true],
            'prototype_name'  => '__pricing__',
            'allow_add'       => true,
            'allow_delete'    => true,
            'add_button_text' => t('pricing.button.add', [], 'EkynaProduct'),
            'required'        => false,
        ], $options);

        $this->form->add('pricings', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the reference field.
     */
    public function addReferenceField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.reference', [], 'EkynaUi'),
            'required' => false,
            'disabled' => !empty($this->product->getReference()),
        ], $options);

        $this->form->add('reference', SF\TextType::class, $options);

        return $this;
    }

    /**
     * Adds the references field.
     */
    public function addReferencesField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'        => t('product_reference.label.plural', [], 'EkynaProduct'),
            'entry_type'   => PR\ProductReferenceType::class,
            'allow_add'    => true,
            'allow_delete' => true,
            'required'     => false,
        ], $options);

        $this->form->add('references', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the seo field.
     */
    public function addSeoField(array $options = []): ProductFormBuilder
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
     */
    public function addSpecialOffersField(array $options = []): ProductFormBuilder
    {
        if (in_array($this->product->getType(), [ProductTypes::TYPE_VARIABLE, ProductTypes::TYPE_CONFIGURABLE])) {
            throw new InvalidArgumentException('Unexpected product type.');
        }

        $options = array_replace([
            'label'           => t('special_offer.label.plural', [], 'EkynaProduct'),
            'entry_type'      => PR\SpecialOffer\SpecialOfferType::class,
            'entry_options'   => ['product_mode' => true],
            'allow_add'       => true,
            'allow_delete'    => true,
            'add_button_text' => t('special_offer.button.add', [], 'EkynaProduct'),
            'required'        => false,
        ], $options);

        $this->form->add('specialOffers', CollectionType::class, $options);

        return $this;
    }

    /**
     * Adds the tags field.
     */
    public function addTagsField(array $options = []): ProductFormBuilder
    {
        $this->form->add('tags', TagChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the translations field.
     */
    public function addTranslationsField(array $options = []): ProductFormBuilder
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
     */
    public function addVariableField(array $options = []): ProductFormBuilder
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
     */
    public function addVisibleField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'    => t('field.visible', [], 'EkynaUi'),
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
     */
    public function addVisibilityField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label' => t('common.visibility', [], 'EkynaProduct'),
        ], $options);

        $this->form->add('visibility', SF\IntegerType::class, $options);

        return $this;
    }

    /**
     * Adds the best-seller field.
     */
    public function addBestSellerField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'   => t('product.field.best_seller', [], 'EkynaProduct'),
            'class'   => HighlightModes::class,
            'select2' => false,
        ], $options);

        $this->form->add('bestSeller', ConstantChoiceType::class, $options);

        return $this;
    }

    /**
     * Adds the cross-selling field.
     */
    public function addCrossSellingField(array $options = []): ProductFormBuilder
    {
        $options = array_replace([
            'label'   => t('product.field.cross_selling', [], 'EkynaProduct'),
            'class'   => HighlightModes::class,
            'select2' => false,
        ], $options);

        $this->form->add('crossSelling', ConstantChoiceType::class, $options);

        return $this;
    }
}
