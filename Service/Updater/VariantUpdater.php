<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Exception\InvalidProductException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function is_null;

/**
 * Class ProductUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantUpdater
{
    private PersistenceHelperInterface     $persistenceHelper;
    private LocaleProviderInterface        $localeProvider;
    private AttributeTypeRegistryInterface $typeRegistry;

    public function __construct(
        PersistenceHelperInterface     $persistenceHelper,
        LocaleProviderInterface        $localeProvider,
        AttributeTypeRegistryInterface $typeRegistry
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->localeProvider = $localeProvider;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Updates the attributes designation and title if needed.
     *
     * @throws CommerceExceptionInterface
     *
     * @TODO Move to a 'VariantDesignationGenerator' service
     */
    public function updateAttributesDesignationAndTitle(Model\ProductInterface $variant): bool
    {
        $this->assertVariantWithParent($variant);

        if (null === $attributeSet = $variant->getParent()->getAttributeSet()) {
            throw new RuntimeException('Variant\'s parent attribute set must be defined.');
        }

        $changed = false;

        // Attributes title for each locale
        foreach ($this->localeProvider->getAvailableLocales() as $locale) {
            $titles = [];
            foreach ($attributeSet->getSlots() as $slot) {
                if (!$slot->isNaming()) {
                    continue;
                }

                foreach ($variant->getAttributes() as $productAttribute) {
                    if ($productAttribute->getAttributeSlot() === $slot) {
                        $attributeType = $this->typeRegistry->getType($slot->getAttribute()->getType());

                        if (!empty($title = $attributeType->render($productAttribute, $locale))) {
                            $titles[] = $title;
                        }

                        continue 2;
                    }
                }

                if ($slot->isRequired()) {
                    throw new InvalidProductException("No attribute found for '{$slot->getAttribute()}'.'");
                }
            }

            $title = trim(implode(' ', $titles));
            // TODO truncate if length is greater than 255 ?

            // If title is not blank or locale is the default one or a translation exists for this locale.
            if (
                !empty($title)
                || $locale === $this->localeProvider->getFallbackLocale()
                || $variant->hasTranslationForLocale($locale)
            ) {
                // Create variant translation
                $vChanged = false;
                $vTrans = $variant->translate($locale, true);

                if ($title !== $vTrans->getAttributesTitle()) {
                    $vTrans->setAttributesTitle($title);
                    $vChanged = true;
                }

                if ($vChanged || is_null($vTrans->getId())) {
                    $this->persistenceHelper->persistAndRecompute($vTrans, false);
                }

                $changed = $changed || $vChanged;
            }
        }

        // Attributes designation
        $names = [];
        $locale = $this->localeProvider->getCurrentLocale(); // TODO Default locale
        foreach ($attributeSet->getSlots() as $slot) {
            if (!$slot->isNaming()) {
                continue;
            }

            foreach ($variant->getAttributes() as $productAttribute) {
                if ($productAttribute->getAttributeSlot() === $slot) {
                    $attributeType = $this->typeRegistry->getType($slot->getAttribute()->getType());

                    if ($attributeType->hasChoices()) {
                        foreach ($productAttribute->getChoices() as $attributeChoice) {
                            $names[] = $attributeChoice->getName();
                        }
                    } elseif (!empty($name = $attributeType->render($productAttribute, $locale))) {
                        $names[] = $name;
                    }

                    continue 2;
                }
            }

            if ($slot->isRequired()) {
                throw new InvalidProductException("No attribute found for '{$slot->getAttribute()}'.'");
            }
        }

        $designation = trim(implode(' ', $names));
        // TODO truncate if length is greater than 255 ?
        if ($designation != $variant->getAttributesDesignation()) {
            $variant->setAttributesDesignation($designation);

            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the tax group regarding its parent/variable product.
     *
     * @throws CommerceExceptionInterface
     */
    public function updateTaxGroup(Model\ProductInterface $variant): bool
    {
        $this->assertVariantWithParent($variant);

        $taxGroup = $variant->getParent()->getTaxGroup();
        if ($variant->getTaxGroup() !== $taxGroup) {
            $variant->setTaxGroup($taxGroup);

            return true;
        }

        return false;
    }

    /**
     * Updates the quantity unit regarding its parent/variable product.
     *
     * @throws CommerceExceptionInterface
     */
    public function updateUnit(Model\ProductInterface $variant): bool
    {
        $this->assertVariantWithParent($variant);

        $unit = $variant->getParent()->getUnit();
        if ($variant->getUnit() !== $unit) {
            $variant->setUnit($unit);

            return true;
        }

        return false;
    }

    /**
     * Updates whether the given variant is physical regarding its parent/variable product.
     *
     * @throws CommerceExceptionInterface
     */
    public function updatePhysical(Model\ProductInterface $variant): bool
    {
        $this->assertVariantWithParent($variant);

        $physical = $variant->getParent()->isPhysical();
        if ($variant->isPhysical() !== $physical) {
            $variant->setPhysical($physical);

            return true;
        }

        return false;
    }

    /**
     * Updates the brand regarding its parent/variable product.
     *
     * @throws CommerceExceptionInterface
     */
    public function updateBrand(Model\ProductInterface $variant): bool
    {
        $this->assertVariantWithParent($variant);

        $brand = $variant->getParent()->getBrand();
        if ($variant->getBrand() !== $brand) {
            $variant->setBrand($brand);

            return true;
        }

        return false;
    }

    /**
     * Updates the given variant availability regarding its parent.
     */
    public function updateAvailability(Model\ProductInterface $variant): bool
    {
        Model\ProductTypes::assertVariant($variant);

        $changed = false;

        $variable = $variant->getParent();

        if (!$variable->isVisible() && $variant->isVisible()) {
            $variant->setVisible(false);
            $changed = true;
        }
        if ($variable->isQuoteOnly() && !$variant->isQuoteOnly()) {
            $variant->setQuoteOnly(true);
            $changed = true;
        }
        if ($variable->isEndOfLife() && !$variant->isEndOfLife()) {
            $variant->setEndOfLife(true);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Asserts that the variant has a parent.
     *
     * @param Model\ProductInterface $variant
     *
     * @throws CommerceExceptionInterface
     */
    protected function assertVariantWithParent(Model\ProductInterface $variant)
    {
        Model\ProductTypes::assertVariant($variant);

        if (null === $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }
    }
}
