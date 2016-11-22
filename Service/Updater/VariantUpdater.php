<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Exception\InvalidProductException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class ProductUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantUpdater
{
    /**
     * Updates the variant designation regarding to his attributes.
     *
     * @param Model\ProductInterface $variant The variant product
     *
     * @return bool Whether the variable has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateDesignation(Model\ProductInterface $variant)
    {
        $this->assertVariantWithParent($variant);

        if (null === $attributeSet = $variant->getParent()->getAttributeSet()) {
            throw new RuntimeException("Variant's parent attribute set must be defined.");
        }

        $attributeNames = [];
        foreach ($attributeSet->getSlots() as $slot) {
            $group = $slot->getGroup();
            $found = false;
            foreach ($variant->getAttributes() as $attribute) {
                if ($attribute->getGroup() === $group) {
                    $attributeNames[] = $attribute->getName();
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

        $designation = implode(' ', $attributeNames);
        if ($designation != $variant->getDesignation()) {
            $variant->setDesignation($designation);

            return true;
        }

        return false;
    }

    /**
     * Updates the tax group regarding to his parent/variable product.
     *
     * @param Model\ProductInterface $variant
     *
     * @return bool Whether the variable has been changed or not.
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
     * Asserts that the variant has a parent.
     *
     * @param Model\ProductInterface $variant
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
