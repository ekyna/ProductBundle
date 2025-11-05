<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Converter;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Form\Type\Convert\SimpleToVariantType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Updater\VariantUpdater;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class SimpleToVariantConverter
 * @package Ekyna\Bundle\ProductBundle\Service\Converter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SimpleToVariantConverter extends AbstractConverter
{
    private readonly VariantUpdater $variantUpdater;

    public function setVariantUpdater(
        PersistenceHelperInterface     $persistenceHelper,
        LocaleProviderInterface        $localeProvider,
        AttributeTypeRegistryInterface $typeRegistry,
    ): void {
        $this->variantUpdater = new VariantUpdater(
            $persistenceHelper,
            $localeProvider,
            $typeRegistry,
        );
    }

    public function supportsSourceType(string $type): bool
    {
        return $type === ProductTypes::TYPE_SIMPLE;
    }

    public function supportsTargetType(string $type): bool
    {
        return $type === ProductTypes::TYPE_VARIANT;
    }

    protected function init(): ProductInterface
    {
        // Attributes
        foreach ($this->source->getAttributes() as $attribute) {
            $this->source->removeAttribute($attribute);
        }

        return $this->source;
    }

    protected function onConvert(): void
    {
        $this->source->setType(ProductTypes::TYPE_VARIANT);

        $this->target->setPendingOffers(true);
        $this->target->setPendingPrices(true);

        $this->target->setAttributeSet(null);
        $this->target->setDesignation(null);
        $this->target->setSeo(null);
        $this->target->setContent(null);

        $this->variantUpdater->updateTaxGroup($this->source);
        $this->variantUpdater->updatePhysical($this->source);
        $this->variantUpdater->updateUnit($this->source);
        $this->variantUpdater->updateBrand($this->source);
        $this->variantUpdater->updateAvailability($this->source);

        // Translations
        foreach ($this->target->getTranslations() as $translation) {
            $translation->clear();
            $translation->setAttributesTitle('TMP');
        }

        // Categories
        foreach ($this->target->getCategories() as $category) {
            $this->target->removeCategory($category);
        }

        // Customer groups
        foreach ($this->target->getCustomerGroups() as $customerGroup) {
            $this->target->removeCustomerGroup($customerGroup);
        }

        // Option groups
        foreach ($this->target->getOptionGroups() as $optionGroup) {
            $this->target->removeOptionGroup($optionGroup);
        }

        // Tags
        foreach ($this->target->getTags() as $tag) {
            $this->target->removeTag($tag);
        }
    }

    protected function buildForm(): FormInterface
    {
        return $this->formFactory->create(SimpleToVariantType::class, $this->target);
    }
}
