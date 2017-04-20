<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use function is_object;

/**
 * Class ProductAttributesTransformer
 * @package Ekyna\Bundle\ProductBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributesTransformer implements DataTransformerInterface
{
    private string $productAttributeClass;
    private ?Model\AttributeSetInterface $attributeSet;

    public function __construct(string $productAttributeClass, ?Model\AttributeSetInterface $attributeSet)
    {
        $this->productAttributeClass = $productAttributeClass;
        $this->attributeSet = $attributeSet;
    }

    /**
     * @inheritDoc
     *
     * Transforms the product attributes collection to an array
     * by creating missing entries and indexing the array by slot id.
     */
    public function transform($value)
    {
        /** @var Collection<Model\ProductAttributeInterface> $value */
        if ($value instanceof Collection) {
            $array = $value->toArray();
        } else {
            $array = [];
        }

        $array = $this->createMissingAttributes($array);

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (!is_array($value)) {
            throw new TransformationFailedException('Expected array');
        }

        $value = $this->createMissingAttributes($value);

        // Remove empty / non required product attributes.
        /** @var Model\ProductAttributeInterface $productAttribute */
        foreach ($value as $key => $productAttribute) {
            if (!$productAttribute->getAttributeSlot()->isRequired() && $productAttribute->isEmpty()) {
                unset($value[$key]);
            }
        }

        return new ArrayCollection(array_values($value));
    }

    /**
     * Creates and add the missing product attributes to the given array.
     *
     * @param array $array
     *
     * @return array
     */
    private function createMissingAttributes(array $array)
    {
        $return = [];

        if (null === $this->attributeSet) {
            return $return;
        }

        foreach ($this->attributeSet->getSlots() as $slot) {
            /** @var Model\ProductAttributeInterface $pa */
            foreach ($array as $pa) {
                if ($pa->getAttributeSlot() === $slot) {
                    $return[] = $pa;

                    continue 2;
                }
            }

            // Create if not found
            /** @var Model\ProductAttributeInterface $productAttribute */
            $productAttribute = new $this->productAttributeClass(); // TODO Product attribute factory ?
            $productAttribute->setAttributeSlot($slot);

            $return[] = $productAttribute;
        }

        return $return;
    }
}
