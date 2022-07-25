<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class AbstractHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractHandler implements HandlerInterface
{
    public function handleInsert(ResourceEventInterface $event): bool
    {
        return false;
    }

    public function handleUpdate(ResourceEventInterface $event): bool
    {
        return false;
    }

    public function handleDelete(ResourceEventInterface $event): bool
    {
        return false;
    }

    public function handleStockUnitChange(SubjectStockUnitEvent $event): bool
    {
        return false;
    }

    public function handleStockUnitRemoval(SubjectStockUnitEvent $event): bool
    {
        return false;
    }

    public function handleChildPriceChange(ResourceEventInterface $event): bool
    {
        return false;
    }

    public function handleChildAvailabilityChange(ResourceEventInterface $event): bool
    {
        return false;
    }

    public function handleChildStockChange(ResourceEventInterface $event): bool
    {
        return false;
    }

    /**
     * Returns the product from the event.
     *
     * @todo Greedy : assertions are made by the 'supports' method.
     */
    protected function getProductFromEvent(
        ResourceEventInterface $event,
        string|array|null      $types = null
    ): ProductInterface {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new UnexpectedTypeException($resource, ProductInterface::class);
        }

        if (null === $types) {
            return $resource;
        }

        $types = (array)$types;

        if (!in_array($resource->getType(), $types, true)) {
            throw new InvalidArgumentException(
                "Expected product with type '" . implode("' or '", $types) . "'."
            );
        }

        return $resource;
    }
}
