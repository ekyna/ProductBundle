<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Ekyna\Bundle\ProductBundle\Exception\ConvertException;
use Ekyna\Bundle\ProductBundle\Form\Type\Convert\VariableType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Symfony\Component\Form\FormInterface;
use Throwable;

/**
 * Class SimpleToVariableConverter
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SimpleToVariableConverter extends AbstractConverter
{
    public function supportsSourceType(string $type): bool
    {
        return $type === ProductTypes::TYPE_SIMPLE;
    }

    public function supportsTargetType(string $type): bool
    {
        return $type === ProductTypes::TYPE_VARIABLE;
    }

    protected function init(): ProductInterface
    {
        // Flag to know if variant data has been cleared (see onConvert / onError)
        $this->set('purged', false);

        $target = $this->productFactory->createWithType(ProductTypes::TYPE_VARIABLE);

        $this->source->setType(ProductTypes::TYPE_VARIANT);
        $this->source->setParent($target);

        // Variable product can't be sold (ie attached to sale items).
        // So we need to keep the product (with its id) as a variant,
        // associated with a new variable product.

        // Pre-load attributes choices
        foreach ($this->source->getAttributes() as $attribute) {
            $attribute->getChoices()->toArray();
        }

        // Attribute Set
        if (null !== $attributeSet = $this->source->getAttributeSet()) {
            $target->setAttributeSet($attributeSet);
        }

        // Brand
        $target->setBrand($this->source->getBrand());

        // Tax group
        $target->setTaxGroup($this->source->getTaxGroup());

        return $target;
    }

    protected function buildForm(): FormInterface
    {
        return $this->formFactory->create(VariableType::class, $this->target);
    }

    protected function onPreConvert(): void
    {
        parent::onPreConvert();

        $this->target
            ->setVisible($this->source->isVisible())
            ->setEndOfLife($this->source->isEndOfLife())
            ->setNotContractual($this->source->isNotContractual())
            ->setBrandNaming($this->source->isBrandNaming());

        // Designation (backup)
        $this->set('designation', $designation = $this->source->getDesignation());
        $this->target->setDesignation($designation);

        // Seo (backup id)
        $seoId = null;
        if (null !== $seo = $this->source->getSeo()) {
            $seoId = $seo->getId();
            $this->target->setSeo($seo);
        }
        $this->set('seoId', $seoId);

        // Content (backup id)
        $contentId = null;
        if (null !== $content = $this->source->getContent()) {
            $contentId = $content->getId();
            $this->target->setContent($content);
        }
        $this->set('contentId', $contentId);

        // Attributes (backup)
        $attributes = [];
        foreach ($this->source->getAttributes() as $attribute) {
            $this->source->removeAttribute($attribute);
            $attributes[] = clone $attribute;
        }
        $this->set('attributes', $attributes);

        // Translations (backup)
        $translations = [];
        $translationClass = null;
        foreach ($this->source->getTranslations() as $translation) {
            if (!$translationClass) {
                $translationClass = get_class($translation);
            }
            $translations[$translation->getId()] = clone $translation;
        }
        $this->set('translations', $translations);
        $this->set('translationClass', $translationClass);
    }

    protected function onConvert(): void
    {
        $translations = $this->get('translations');
        $translationClass = $this->get('translationClass');

        $this->entityManager->beginTransaction();
        try {
            // Clear variant's designation, seo and content to prevent unique constraints errors.
            $this->entityManager->createQuery(
                'UPDATE ' . get_class($this->source) . ' p ' .
                'SET p.designation = null, p.seo = null, p.content = null ' .
                'WHERE p.id = :id'
            )->execute([
                'id' => $this->source->getId(),
            ]);
            // Clear variant's translations to prevent unique constraints errors.
            if (!empty($translationClass) && !empty($translations)) {
                $q = $this->entityManager->createQuery(
                    'UPDATE ' . $translationClass . ' t ' .
                    'SET t.title = null,' .
                    '    t.attributesTitle = null,' .
                    '    t.subTitle = null,' .
                    '    t.description = null,' .
                    '    t.slug = null ' .
                    'WHERE t.id IN (:id)'
                );
                $q->setMaxResults(count($translations));
                $q->execute(['id' => array_keys($translations)]);
            }
            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            throw new ConvertException('Failed to clear variant data.', 0, $e);
        }

        $this->set('purged', true);

        // Reload the variant and reapply changes
        $this->entityManager->refresh($this->source);

        // Add variant to variable
        $this->source->setType(ProductTypes::TYPE_VARIANT);
        $this->source->setParent($this->target);

        $this->source->setAttributeSet(null);

        // Restore attributes
        foreach ($this->get('attributes') as $attribute) {
            $this->source->addAttribute($attribute);
        }

        // Translations (variable)
        /** @var ProductTranslationInterface $translation */
        foreach ($this->get('translations') as $translation) {
            $this->target->addTranslation($translation);
        }
        // Translations (variant)
        foreach ($this->source->getTranslations() as $translation) {
            $translation->clear();
            $translation->setAttributesTitle('TMP'); // Prevent removal by the TranslatableListener
        }

        // Categories
        foreach ($this->source->getCategories() as $category) {
            $this->target->addCategory($category);
            $this->source->removeCategory($category);
        }

        // Customer groups
        foreach ($this->source->getCustomerGroups() as $customerGroup) {
            $this->target->addCustomerGroup($customerGroup);
            $this->source->removeCustomerGroup($customerGroup);
        }

        $form = $this->getForm();

        // Option groups
        if ($form->has('option_group_selection')) {
            $optionGroupIds = $form->get('option_group_selection')->getData();
            foreach ($this->source->getOptionGroups() as $optionGroup) {
                if (in_array($optionGroup->getId(), $optionGroupIds)) {
                    $this->source->removeOptionGroup($optionGroup);
                    $this->target->addOptionGroup(clone $optionGroup);
                }
            }
        }

        // Medias
        if ($form->has('media_selection')) {
            $mediaIds = $form->get('media_selection')->getData();
            foreach ($this->source->getMedias() as $media) {
                if (in_array($media->getId(), $mediaIds)) {
                    $this->source->removeMedia($media);
                    $this->target->addMedia(clone $media);
                }
            }
        }

        // Tags
        if ($form->has('tag_selection')) {
            $tagIds = $form->get('tag_selection')->getData();
            foreach ($this->source->getTags() as $tag) {
                if (in_array($tag->getId(), $tagIds)) {
                    $this->source->removeTag($tag);
                    $this->target->addTag($tag);
                }
            }
        }
    }

    protected function onDoneConvert(): void
    {
        parent::onDoneConvert();

        $this->getEvent()->addMessage(ResourceMessage::create(
            'convert.simple_to_variable.warning',
            ResourceMessage::TYPE_WARNING
        )->setDomain('EkynaProduct'));
    }

    protected function onError(): void
    {
        parent::onError();

        if (!$this->get('purged')) {
            return;
        }

        // Restore product's designation, seo and content
        $this->entityManager->createQuery(
            'UPDATE ' . get_class($this->source) . ' p ' .
            'SET p.designation = :designation, p.seo = :seo, p.content = :content ' .
            'WHERE p.id = :id'
        )->execute([
            'designation' => $this->get('designation'),
            'seo'         => $this->get('seoId'),
            'content'     => $this->get('contentId'),
            'id'          => $this->source->getId(),
        ]);

        $translations = $this->get('translations');
        $translationClass = $this->get('translationClass');

        // Restore product's translations slugs
        if (!empty($translationClass)) {
            $q = $this->entityManager->createQuery(
                'UPDATE ' . $translationClass . ' t ' .
                'SET t.title = :title,' .
                '    t.attributesTitle = :attr_title,' .
                '    t.subTitle = :sub_title,' .
                '    t.description = :description,' .
                '    t.slug = :slug ' .
                'WHERE t.id = :id'
            );
            /** @var ProductTranslationInterface $translation */
            foreach ($translations as $id => $translation) {
                $q->execute([
                    'id'          => $id,
                    'title'       => $translation->getTitle(),
                    'attr_title'  => $translation->getAttributesTitle(),
                    'sub_title'   => $translation->getSubTitle(),
                    'description' => $translation->getDescription(),
                    'slug'        => $translation->getSlug(),
                ]);
            }
        }
    }
}
