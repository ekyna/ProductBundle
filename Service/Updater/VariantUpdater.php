<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Bundle\ProductBundle\Exception\InvalidProductException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantUpdater
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param LocaleProviderInterface    $localeProvider
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        LocaleProviderInterface $localeProvider
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->localeProvider = $localeProvider;
    }

    /**
     * Updates the attributes designation and title if needed.
     *
     * @param Model\ProductInterface $variant The variant product
     *
     * @return bool Whether the variant has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateAttributesDesignationAndTitle(Model\ProductInterface $variant)
    {
        $this->assertVariantWithParent($variant);

        if (null === $attributeSet = $variant->getParent()->getAttributeSet()) {
            throw new RuntimeException("Variant's parent attribute set must be defined.");
        }

        $changed = false;

        // Attributes title for each locales
        foreach ($this->localeProvider->getAvailableLocales() as $locale) {
            $titles = [];
            foreach ($attributeSet->getSlots() as $slot) {
                $group = $slot->getGroup();
                $found = false;
                foreach ($variant->getAttributes() as $attribute) {
                    if ($attribute->getGroup() === $group) {
                        // Don't create attribute translation here
                        if (null !== $aTrans = $attribute->getTranslations()->get($locale)) {
                            if (0 < strlen($title = $aTrans->getTitle())) {
                                $titles[] = $title;
                            }
                        } else {
                            // TODO missing trans ?
                        }
                        $found = true;
                        if (!$slot->isMultiple()) {
                            continue 2;
                        }
                    }
                }
                if ($slot->isRequired() && !$found) {
                    throw new InvalidProductException("No attribute found for attribute group '$group'.'");
                }
            }

            $title = trim(implode(' ', $titles));
            // TODO truncate if length is greater than 255 ?

            // If title is not blank or locale is the default one or a translation exists for this locale.
            if (
                0 < strlen($title) ||
                $locale == $this->localeProvider->getFallbackLocale() ||
                null !== $variant->getTranslations()->get($locale)
            ) {
                // Create variant translation
                $vTrans = $variant->translate($locale, true);

                if ($title != $vTrans->getAttributesTitle()) {
                    $vTrans->setAttributesTitle($title);

                    $this->persistTranslation($vTrans);

                    $changed = true;
                }
            }
        }

        // Attributes designation
        $names = [];
        foreach ($attributeSet->getSlots() as $slot) {
            $group = $slot->getGroup();
            $found = false;
            foreach ($variant->getAttributes() as $attribute) {
                if ($attribute->getGroup() === $group) {
                    $names[] = $attribute->getName();
                    $found = true;
                    if (!$slot->isMultiple()) {
                        continue 2;
                    }
                }
            }
            if ($slot->isRequired() && !$found) {
                throw new InvalidProductException("No attribute found for attribute group '$group'.'");
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
     * Persists and recomputes the translation.
     *
     * @param $translation
     */
    protected function persistTranslation($translation)
    {
        $manager = $this->persistenceHelper->getManager();
        $uow = $manager->getUnitOfWork();

        if (!($uow->isScheduledForInsert($translation) || $uow->isScheduledForUpdate($translation))) {
            $manager->persist($translation);
        }

        $metadata = $manager->getClassMetadata(get_class($translation));
        if ($uow->getEntityChangeSet($translation)) {
            $uow->recomputeSingleEntityChangeSet($metadata, $translation);
        } else {
            $uow->computeChangeSet($metadata, $translation);
        }
    }

    /**
     * Updates the tax group regarding to his parent/variable product.
     *
     * @param Model\ProductInterface $variant
     *
     * @return bool Whether the variant has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateTaxGroup(Model\ProductInterface $variant)
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
     * Updates the brand regarding to his parent/variable product.
     *
     * @param Model\ProductInterface $variant
     *
     * @return bool Whether the variant has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateBrand(Model\ProductInterface $variant)
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
     * Asserts that the variant has a parent.
     *
     * @param Model\ProductInterface $variant
     *
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    protected function assertVariantWithParent(Model\ProductInterface $variant)
    {
        Model\ProductTypes::assertVariant($variant);

        if (null === $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }
    }
}
