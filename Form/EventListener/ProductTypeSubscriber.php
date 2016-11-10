<?php

namespace Ekyna\Bundle\ProductBundle\Form\EventListener;

use A2lix\TranslationFormBundle\Form\Type\TranslationsFormsType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Ekyna\Bundle\CmsBundle\Form\Type\SeoType;
use Ekyna\Bundle\CommerceBundle\Form\Type\TaxGroupChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\ProductBundle\Form\Type\BundleSlotsType;
use Ekyna\Bundle\ProductBundle\Form\Type\OptionGroupType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductAttributesType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductReferenceType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductTranslationType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes as Types;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

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
    private $productClass;

    /**
     * @var string
     */
    private $attributeSetClass;


    /**
     * Constructor.
     *
     * @param string $productClass
     * @param string $attributeSetClass
     */
    public function __construct($productClass, $attributeSetClass)
    {
        $this->productClass = $productClass;
        $this->attributeSetClass = $attributeSetClass;
    }

    /**
     * Form pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $product = $event->getData();

        $type = $product->getType();
        if (!ProductTypes::isValid($type)) {
            throw new \RuntimeException('Product type not set or invalid.');
        }

        $form->add('seo', SeoType::class, [
            'label' => false,
            'required' => $type != Types::TYPE_VARIANT,
        ]);

        switch ($type) {
            case Types::TYPE_SIMPLE:
                $this->buildSimpleProductForm($form, $product);
                break;
            case Types::TYPE_VARIANT:
                $this->buildVariantProductForm($form, $product);
                break;
            case Types::TYPE_VARIABLE:
                $this->buildVariableProductForm($form, $product);
                break;
            case Types::TYPE_BUNDLE:
                $this->buildBundleProductForm($form, $product);
                break;
            case Types::TYPE_CONFIGURABLE:
                $this->buildConfigurableProductForm($form, $product);
                break;
            default:
                throw new \InvalidArgumentException('Unexpected product type.');
        }
    }


    /**
     * Builds the simple product form.
     *
     * @param FormInterface    $form
     * @param ProductInterface $product
     */
    protected function buildSimpleProductForm(FormInterface $form, ProductInterface $product)
    {
        $form
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => ProductTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('designation', Type\TextType::class, [
                'label' => 'ekyna_core.field.designation',
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
            ])
            ->add('weight', Type\NumberType::class, [
                'label'  => 'ekyna_core.field.weight',
                'scale'  => 3,
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                ],
            ])
            ->add('netPrice', Type\NumberType::class, [
                'label'  => 'ekyna_product.product.field.net_price',
                'scale'  => 5,
                'attr'   => [
                    'input_group' => ['append' => '€'],
                ],
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'allow_new' => true,
            ])
            ->add('references', CollectionType::class, [
                'label'      => 'ekyna_product.product_reference.label.plural',
                'entry_type' => ProductReferenceType::class,
                'required'   => false,
            ]);

        $this->addOptionGroupsForm($form);
    }

    /**
     * Builds the bundle product form.
     *
     * @param FormInterface    $form
     * @param ProductInterface $product
     */
    protected function buildVariantProductForm(FormInterface $form, ProductInterface $product)
    {
        $form
            ->add('variable', ResourceType::class, [
                'label'         => 'ekyna_product.product.field.parent',
                'property_path' => 'parent',
                'class'         => $this->productClass,
                'required'      => false,
                'disabled'      => true,
            ])
            ->add('designation', Type\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.variant.help.leave_blank_to_auto_generate',
                ],
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
            ])
            ->add('weight', Type\NumberType::class, [
                'label'  => 'ekyna_core.field.weight',
                'scale'  => 3,
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                ],
            ])
            ->add('netPrice', Type\NumberType::class, [
                'label'  => 'ekyna_product.product.field.net_price',
                'scale'  => 5,
                'attr'   => [
                    'input_group' => ['append' => '€'],
                ],
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'required' => false,
                'disabled' => true,
            ])
            ->add('attributes', ProductAttributesType::class, [
                'label'         => 'ekyna_product.attribute.label.plural',
                'attribute_set' => $product->getParent()->getAttributeSet(),
            ])
            ->add('references', CollectionType::class, [
                'label'      => 'ekyna_product.product_reference.label.plural',
                'entry_type' => ProductReferenceType::class,
                'required'   => false,
            ]);

        $this->addOptionGroupsForm($form);
    }

    /**
     * Builds the variable product form.
     *
     * @param FormInterface    $form
     * @param ProductInterface $product
     */
    protected function buildVariableProductForm(FormInterface $form, ProductInterface $product)
    {
        $form
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => ProductTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('designation', Type\TextType::class, [
                'label' => 'ekyna_core.field.designation',
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
            ])
            ->add('netPrice', Type\NumberType::class, [
                'label'    => 'ekyna_product.product.field.net_price', // TODO
                'disabled' => true,
                'scale'    => 5,
                'attr'     => [
                    'input_group' => ['append' => '€'],
                ],
            ])
            ->add('taxGroup', TaxGroupChoiceType::class, [
                'allow_new' => true,
            ])
            ->add('attributeSet', ResourceType::class, [
                'label'     => 'ekyna_product.attribute_set.label.singular',
                'class'     => $this->attributeSetClass,
                'allow_new' => true,
            ]);
    }

    /**
     * Builds the bundle product form.
     *
     * @param FormInterface    $form
     * @param ProductInterface $product
     */
    protected function buildBundleProductForm(FormInterface $form, ProductInterface $product)
    {
        $form
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => ProductTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('designation', Type\TextType::class, [
                'label' => 'ekyna_core.field.designation',
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
            ]);

        $this->addBundleSlotsForm($form, false);
    }

    /**
     * Builds the configurable product form.
     *
     * @param FormInterface    $form
     * @param ProductInterface $product
     */
    protected function buildConfigurableProductForm(FormInterface $form, ProductInterface $product)
    {
        $form
            ->add('translations', TranslationsFormsType::class, [
                'form_type'      => ProductTranslationType::class,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->add('designation', Type\TextType::class, [
                'label' => 'ekyna_core.field.designation',
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
            ]);

        $this->addBundleSlotsForm($form, true);
    }

    /**
     * Adds the option groups form.
     *
     * @param FormInterface $form
     */
    protected function addOptionGroupsForm(FormInterface $form)
    {
        $form->add('optionGroups', CollectionType::class, [
            'label'           => 'ekyna_product.option_group.label.plural',
            'prototype_name'  => '__option_group__',
            'sub_widget_col'  => 11,
            'button_col'      => 1,
            'allow_sort'      => true,
            'entry_type'      => OptionGroupType::class,
            'add_button_text' => 'ekyna_product.option_group.button.add',
            'required'        => false,
        ]);
    }

    /**
     * Adds the bundle slots form.
     *
     * @param FormInterface $form
     * @param bool          $configurable
     */
    protected function addBundleSlotsForm(FormInterface $form, $configurable = false)
    {
        $form->add('bundleSlots', BundleSlotsType::class, [
            'configurable' => $configurable,
        ]);
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
