<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\BundleSlotEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BundleSlotListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotListener implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleSlotInterface
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $slot = $this->getBundleSlotFromEvent($event);

        $this->scheduleChildPriceChangeEvent($slot->getBundle());

        return $slot;
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleSlotInterface
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $slot = $this->getBundleSlotFromEvent($event);

        if ($this->persistenceHelper->isChanged($slot, ['required'])) {
            $this->scheduleChildPriceChangeEvent($slot->getBundle());
        }

        return $slot;
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleSlotInterface
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $slot = $this->getBundleSlotFromEvent($event);

        // Get bundle from change set if null
        if (null === $bundle = $slot->getBundle()) {
            $bundle = $this->persistenceHelper->getChangeSet($slot, 'bundle')[0];
        }

        $this->scheduleChildPriceChangeEvent($bundle);

        return $slot;
    }

    /**
     * Dispatches the child price change events.
     *
     * @param ProductInterface $bundle
     */
    private function scheduleChildPriceChangeEvent(ProductInterface $bundle)
    {
        ProductTypes::assertBundled($bundle);

        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_PRICE_CHANGE, $bundle);
    }

    /**
     * Returns the bundle slot from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleSlotInterface
     * @throws InvalidArgumentException
     */
    protected function getBundleSlotFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof BundleSlotInterface) {
            throw new InvalidArgumentException('Expected instance of ' . BundleSlotInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            BundleSlotEvents::INSERT => ['onInsert', 0],
            BundleSlotEvents::UPDATE => ['onUpdate', 0],
            BundleSlotEvents::DELETE => ['onDelete', 0],
        ];
    }
}
