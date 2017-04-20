<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductStockUnitEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductStockUnitInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\EventListener\AbstractStockUnitListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductStockUnitListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitListener extends AbstractStockUnitListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    protected function getStockUnitFromEvent(ResourceEventInterface $event)
    {
        $stockUnit = $event->getResource();

        if (!$stockUnit instanceof ProductStockUnitInterface) {
            throw new InvalidArgumentException("Expected instance of ProductStockUnitInterface.");
        }

        return $stockUnit;
    }

    /**
     * @inheritDoc
     */
    protected function getSubjectStockUnitChangeEventName()
    {
        return ProductEvents::STOCK_UNIT_CHANGE;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductStockUnitEvents::INSERT => ['onInsert', 0],
            ProductStockUnitEvents::UPDATE => ['onUpdate', 0],
            ProductStockUnitEvents::DELETE => ['onDelete', 0],
        ];
    }
}
