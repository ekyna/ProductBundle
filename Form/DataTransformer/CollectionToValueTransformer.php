<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class CollectionToValueTransformer
 * @package Ekyna\Bundle\ProductBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CollectionToValueTransformer implements DataTransformerInterface
{
    /**
     * Transforms a collection into an single value.
     *
     * @param Collection $value A collection of entities
     *
     * @return mixed The first entity
     *
     * @throws TransformationFailedException
     */
    public function transform($value)
    {
        if (null === $value) {
            return [];
        }

        // For cases when the collection getter returns $collection->toArray()
        // in order to prevent modifications of the returned collection
        if (is_array($value)) {
            return $value;
        }

        if (!$value instanceof Collection) {
            throw new TransformationFailedException('Expected a Doctrine\Common\Collections\Collection object.');
        }

        return $value->first();
    }

    /**
     * Transforms choice keys into entities.
     *
     * @param mixed $entity An array of entities
     *
     * @return Collection A collection of entities
     */
    public function reverseTransform($entity)
    {
        if ('' === $entity || null === $entity) {
            $entity = [];
        } else {
            $entity = [$entity];
        }

        return new ArrayCollection($entity);
    }
}
