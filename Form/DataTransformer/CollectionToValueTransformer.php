<?php

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
     * @param Collection $collection A collection of entities
     *
     * @return mixed The first entity
     *
     * @throws TransformationFailedException
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return [];
        }

        // For cases when the collection getter returns $collection->toArray()
        // in order to prevent modifications of the returned collection
        if (is_array($collection)) {
            return $collection;
        }

        if (!$collection instanceof Collection) {
            throw new TransformationFailedException('Expected a Doctrine\Common\Collections\Collection object.');
        }

        return $collection->first();
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