<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

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
    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleDelete(ResourceEventInterface $event)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleStockUnitChange(SubjectStockUnitEvent $event)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleStockUnitRemoval(SubjectStockUnitEvent $event)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildPriceChange(ResourceEventInterface $event)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildAvailabilityChange(ResourceEventInterface $event)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        return false;
    }

    /**
     * Returns the product from the event.
     *
     * @param ResourceEventInterface $event
     * @param string|array           $types
     *
     * @todo Greedy : assertions are made by the 'supports' method.
     *
     * @return ProductInterface
     */
    protected function getProductFromEvent(ResourceEventInterface $event, $types = null)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new InvalidArgumentException("Expected ProductInterface");
        }

        if (null !== $types && !in_array($resource->getType(), (array) $types)) {
            throw new InvalidArgumentException(
                "Expected product with type '" . implode("' or '", (array) $types) . "'."
            );
        }

        return $resource;
    }
}
