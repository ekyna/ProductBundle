<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductStockUnitEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductStockUnitInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\EventListener\AbstractStockUnitListener;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductStockUnitListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitListener extends AbstractStockUnitListener implements EventSubscriberInterface
{
    protected function getStockUnitFromEvent(ResourceEventInterface $event): StockUnitInterface
    {
        $stockUnit = $event->getResource();

        if (!$stockUnit instanceof ProductStockUnitInterface) {
            throw new InvalidArgumentException('Expected instance of ProductStockUnitInterface.');
        }

        return $stockUnit;
    }

    protected function getSubjectStockUnitChangeEventName(): string
    {
        return ProductEvents::STOCK_UNIT_CHANGE;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductStockUnitEvents::INSERT => ['onInsert', 0],
            ProductStockUnitEvents::UPDATE => ['onUpdate', 0],
            ProductStockUnitEvents::DELETE => ['onDelete', 0],
        ];
    }
}
