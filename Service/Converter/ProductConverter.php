<?php

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Ekyna\Bundle\ProductBundle\Form\Type\Convert\VariableType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ProductConverter
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductConverter
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param FormFactoryInterface       $formFactory
     * @param RequestStack               $requestStack
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack
    ) {
        $this->productRepository = $productRepository;
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
    }

    /**
     * Converts the given product to the given type.
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @return \Symfony\Component\Form\FormInterface|ProductInterface
     */
    public function convert(ProductInterface $product, $type)
    {
        ProductTypes::isValid($type);

        if ($product->getType() !== $type) {
            if (null !== $callable = $this->getCallable($product, $type)) {
                return call_user_func($callable, $product);
            }
        }

        return $product;
    }

    /**
     * Returns whether or not the conversion is supported.
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @return bool
     */
    public function can(ProductInterface $product, $type)
    {
        return ($product->getType() !== $type) && (null !== $this->getCallable($product, $type));
    }

    /**
     * Converts a simple product to a variable product.
     *
     * @param ProductInterface $product
     *
     * @return \Symfony\Component\Form\FormInterface|ProductInterface
     */
    protected function convertSimpleToVariable(ProductInterface $product)
    {
        // Variable product can't be sold (ie attached to sale items).
        // So we need to keep the product (with its id) as a variant,
        // associated with a new variable product.

        /** @var ProductInterface $variable */
        $variable = $this->productRepository->createNew();
        $variable->setType(ProductTypes::TYPE_VARIABLE);
        $product->setType(ProductTypes::TYPE_VARIANT);

        // Designation
        $variable->setDesignation($product->getDesignation());
        $product->setDesignation(null);

        // Brand
        $variable->setBrand($product->getBrand());

        // Categories
        foreach ($product->getCategories() as $category) {
            $variable->addCategory($category);
            $product->removeCategory($category);
        }

        // Tax group
        $variable->setTaxGroup($product->getTaxGroup());

        // Customer groups
        foreach ($product->getCustomerGroups() as $customerGroup) {
            $product->removeCustomerGroup($customerGroup);
            $variable->addCustomerGroup($customerGroup);
        }

        // Translations
        /** @var ProductTranslationInterface $translation */
        foreach ($product->getTranslations() as $translation) {
            $variable->addTranslation(clone $translation);
            $translation->clear();
            $translation->setAttributesTitle('TMP'); // Prevent removal by the TranslatableListener
        }

        // Seo
        if (null !== $seo = $product->getSeo()) {
            $variable->setSeo(clone $seo);
            $product->setSeo(null);
        }

        // Content
        if (null !== $content = $product->getContent()) {
            $variable->setContent(clone $content);
            $product->setContent(null);
        }

        // Attribute Set
        $lockAttributeSet = false;
        if (null !== $attributeSet = $product->getAttributeSet()) {
            $variable->setAttributeSet($attributeSet);
            $product->setAttributeSet(null);
            $lockAttributeSet = 0 < $product->getAttributes()->count();
        }

        // Pre load attributes choices
        foreach ($product->getAttributes() as $attribute) {
            $attribute->getChoices()->toArray();
        }

        // Add variant to variable
        $variable->addVariant($product);

        // Form
        $form = $this->formFactory->create(VariableType::class, $variable, [
            'lock_attribute_set' => $lockAttributeSet,
        ]);

        $form->handleRequest($this->requestStack->getMasterRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            // Option groups
            if ($form->has('option_group_selection')) {
                $optionGroupIds = $form->get('option_group_selection')->getData();
                foreach ($product->getOptionGroups() as $optionGroup) {
                    if (in_array($optionGroup->getId(), $optionGroupIds)) {
                        $product->removeOptionGroup($optionGroup);
                        $variable->addOptionGroup(clone $optionGroup);
                    }
                }
            }

            // Medias
            if ($form->has('media_selection')) {
                $mediaIds = $form->get('media_selection')->getData();
                foreach ($product->getMedias() as $media) {
                    if (in_array($media->getId(), $mediaIds)) {
                        $product->removeMedia($media);
                        $variable->addMedia(clone $media);
                    }
                }
            }

            // Tags
            if ($form->has('tag_selection')) {
                $tagIds = $form->get('tag_selection')->getData();
                foreach ($product->getTags() as $tag) {
                    if (in_array($tag->getId(), $tagIds)) {
                        $product->removeTag($tag);
                        $variable->addTag($tag);
                    }
                }
            }

            return $variable;
        }

        return $form;
    }

    /**
     * Returns the method that matches the conversion.
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @return callable|null
     */
    private function getCallable(ProductInterface $product, $type)
    {
        $callable = [$this, sprintf('convert%sTo%s', ucfirst($product->getType()), ucfirst($type))];

        return is_callable($callable) ? $callable : null;
    }
}
