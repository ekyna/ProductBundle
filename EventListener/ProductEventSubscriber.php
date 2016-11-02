<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\EventListener\Handler\HandlerInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var Handler\HandlerRegistry
     */
    private $handlerRegistry;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param Handler\HandlerRegistry    $registry
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        Handler\HandlerRegistry $registry
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->handlerRegistry = $registry;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->executeHandlers($event, HandlerInterface::INSERT);

        // TODO Timestampable behavior/listener
        $product
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if ($changed || true) { // TODO
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->executeHandlers($event, HandlerInterface::UPDATE);

        // TODO Timestampable behavior/listener
        $product->setUpdatedAt(new \DateTime());

        if ($changed || true) { // TODO
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $this->executeHandlers($event, HandlerInterface::DELETE);
    }

    /**
     * Stock unit change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStockUnitChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::STOCK_UNIT_CHANGE)) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Stock unit delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStockUnitRemoval(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::STOCK_UNIT_REMOVAL)) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Stock unit change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onChildStockChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::CHILD_STOCK_CHANGE)) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Execute the event handlers method regarding to the product type,
     * and returns whether or the product has been changed.
     *
     * @param ResourceEventInterface $event
     * @param string                 $method
     *
     * @return bool
     */
    private function executeHandlers(ResourceEventInterface $event, $method)
    {
        $product = $this->getProductFromEvent($event);

        $changed = false;

        $handlers = $this->handlerRegistry->getHandlers($product);
        foreach ($handlers as $handler) {
            $changed = call_user_func([$handler, $method], $event) || $changed;
        }

        return $changed;
    }

    /**
     * Returns the product from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ProductInterface
     */
    private function getProductFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected ProductInterface');
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::INSERT             => ['onInsert', 0],
            ProductEvents::UPDATE             => ['onUpdate', 0],
            ProductEvents::DELETE             => ['onDelete', 0],
            ProductEvents::STOCK_UNIT_CHANGE  => ['onStockUnitChange', 0],
            ProductEvents::STOCK_UNIT_REMOVAL => ['onStockUnitRemoval', 0],
            ProductEvents::CHILD_STOCK_CHANGE => ['onChildStockChange', 0],
        ];
    }
}
