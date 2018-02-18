<?php

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class ProductAttributesTransformer
 * @package Ekyna\Bundle\ProductBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributesTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $productAttributeClass;

    /**
     * @var Model\AttributeSetInterface
     */
    private $attributeSet;


    /**
     * Constructor.
     *
     * @param string                      $productAttributeClass
     * @param Model\AttributeSetInterface $attributeSet
     */
    public function __construct($productAttributeClass, Model\AttributeSetInterface $attributeSet = null)
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
    public function transform($collection)
    {
        /** @var Model\ProductAttributeInterface[] $collection */
        if ($collection instanceof Collection) {
            $array = $collection->toArray();
        } else {
            $array = [];
        }

        $array = $this->createMissingAttributes($array);

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($array)
    {
        if (!is_array($array)) {
            throw new TransformationFailedException('Expected array');
        }

        $array = $this->createMissingAttributes($array);

        // Remove empty / non required product attributes.
        /** @var Model\ProductAttributeInterface $productAttribute */
        foreach ($array as $key => $productAttribute) {
            if (!$productAttribute->getAttributeSlot()->isRequired() && $productAttribute->isEmpty()) {
                unset($array[$key]);
            }
        }

        return new ArrayCollection(array_values($array));
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
            $productAttribute = new $this->productAttributeClass;
            $productAttribute->setAttributeSlot($slot);

            $return[] = $productAttribute;
        }

        return $return;
    }
}
