<?php

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Form\Type\Convert\VariableType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var EntityManagerInterface
     */
    private $productManager;

    /**
     * @var ResourceOperatorInterface
     */
    private $productOperator;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ValidatorInterface
     */
    private $validator;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param EntityManagerInterface     $productManager
     * @param ResourceOperatorInterface  $productOperator
     * @param FormFactoryInterface       $formFactory
     * @param RequestStack               $requestStack
     * @param ValidatorInterface         $validator
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EntityManagerInterface $productManager,
        ResourceOperatorInterface $productOperator,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        ValidatorInterface $validator
    ) {
        $this->productRepository = $productRepository;
        $this->productManager = $productManager;
        $this->productOperator = $productOperator;
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->validator = $validator;
    }

    /**
     * Converts the given product to the given type.
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @return FormInterface|ProductInterface
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
     * @return FormInterface|ResourceEventInterface|ProductInterface
     */
    protected function convertSimpleToVariable(ProductInterface $product)
    {
        // Variable product can't be sold (ie attached to sale items).
        // So we need to keep the product (with its id) as a variant,
        // associated with a new variable product.

        /** @var ProductInterface $variable */
        $variable = $this->productRepository->createNew();
        $product->setType(ProductTypes::TYPE_VARIANT);

        // Attribute Set
        if (null !== $attributeSet = $product->getAttributeSet()) {
            $variable->setAttributeSet($attributeSet);
        }

        // Pre load attributes choices
        foreach ($product->getAttributes() as $attribute) {
            $attribute->getChoices()->toArray();
        }

        // Add variant to variable
        $variable->addVariant($product);

        // Form
        $form = $this->formFactory->create(VariableType::class, $variable);

        $form->handleRequest($this->requestStack->getMasterRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $variable
                ->setType(ProductTypes::TYPE_VARIABLE)
                ->setVisible($product->isVisible())
                ->setQuoteOnly($product->isQuoteOnly())
                ->setEndOfLife($product->isEndOfLife());

            // Designation (backup)
            $variable->setDesignation($designation = $product->getDesignation());

            // Seo (backup id)
            $seoId = null;
            if (null !== $seo = $product->getSeo()) {
                $seoId = $seo->getId();
                $variable->setSeo($seo);
            }

            // Content (backup id)
            $contentId = null;
            if (null !== $content = $product->getContent()) {
                $contentId = $content->getId();
                $variable->setContent($content);
            }

            // Attributes (backup)
            $attributes = [];
            foreach ($product->getAttributes() as $attribute) {
                $product->removeAttribute($attribute);
                $attributes[] = clone $attribute;
            }

            // Translations (slug backup)
            $slugs = [];
            $translationClass = null;
            foreach ($product->getTranslations() as $translation) {
                if (!$translationClass) {
                    $translationClass = get_class($translation);
                }
                $slugs[$translation->getId()] = $translation->getSlug();
            }

            $this->productManager->beginTransaction();
            try {
                // Clear variant's designation, seo and content to prevent unique constraints errors.
                $this->productManager->createQuery(
                    'UPDATE ' . get_class($product) . ' p ' .
                    'SET p.designation = null, p.seo = null, p.content = null ' .
                    'WHERE p.id = :id'
                )->execute([
                    'id' => $product->getId(),
                ]);
                // Clear product's translations slugs
                if (!empty($translationClass)) {
                    $q = $this->productManager->createQuery(
                        'UPDATE ' . $translationClass . ' t ' .
                        'SET t.slug = null WHERE t.id = :id'
                    );
                    $q->setMaxResults(1);
                    foreach (array_keys($slugs) as $id) {
                        $q->execute(['id' => $id]);
                    }
                }
                $this->productManager->commit();
            } catch (\Exception $e) {
                $this->productManager->rollback();

                return $form;
            }

            // Reload the variant and reapply changes
            $this->productManager->refresh($product);

            $product
                ->setType(ProductTypes::TYPE_VARIANT)
                ->setParent($variable)
                ->setAttributeSet(null);

            // Restore attributes
            foreach ($attributes as $attribute) {
                $product->addAttribute($attribute);
            }

            // Brand
            $variable->setBrand($product->getBrand());

            // Tax group
            $variable->setTaxGroup($product->getTaxGroup());

            // Categories
            foreach ($product->getCategories() as $category) {
                $variable->addCategory($category);
                $product->removeCategory($category);
            }

            // Customer groups
            foreach ($product->getCustomerGroups() as $customerGroup) {
                $variable->addCustomerGroup($customerGroup);
                $product->removeCustomerGroup($customerGroup);
            }

            // Translations
            /** @var ProductTranslationInterface $translation */
            foreach ($product->getTranslations() as $translation) {
                $variable->addTranslation(clone $translation);
                $translation->clear();
                $translation->setAttributesTitle('TMP'); // Prevent removal by the TranslatableListener
            }

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

            $variableViolations = $this->validator->validate($variable, null, ['Default', ProductTypes::TYPE_VARIABLE]);
            $variantViolations = $this->validator->validate($product, null, ['Default', ProductTypes::TYPE_VARIANT]);
            if (0 < $variableViolations->count() || 0 < $variantViolations->count()) {
                $event = new ResourceEvent();
                // TODO Message
                $event->addMessage(new ResourceMessage('Failure', ResourceMessage::TYPE_ERROR));
            } else {
                // Persist both variable and variant
                $event = $this->productOperator->create($variable);
            }

            if ($event->hasErrors()) {
                // Restore product's designation, seo and content
                $this->productManager->createQuery(
                    'UPDATE ' . get_class($product) . ' p ' .
                    'SET p.designation = :designation, p.seo = :seo, p.content = :content ' .
                    'WHERE p.id = :id'
                )->execute([
                    'designation' => $designation,
                    'seo'         => $seoId,
                    'content'     => $contentId,
                    'id'          => $product->getId(),
                ]);
                // Restore product's translations slugs
                if (!empty($translationClass)) {
                    $q = $this->productManager->createQuery(
                        'UPDATE ' . $translationClass . ' t ' .
                        'SET t.slug = :slug WHERE t.id = :id'
                    );
                    foreach ($slugs as $id => $slug) {
                        $q->execute([
                            'id'   => $id,
                            'slug' => $slug,
                        ]);
                    }
                }

                return $event;
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
