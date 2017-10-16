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
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $bundleSlot = $this->getBundleSlotFromEvent($event);

        $this->scheduleChildDataChangeEvent($bundleSlot->getBundle());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $bundleSlot = $this->getBundleSlotFromEvent($event);

        if ($this->persistenceHelper->isChanged($bundleSlot, ['required'])) {
            $this->scheduleChildDataChangeEvent($bundleSlot->getBundle());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $bundleSlot = $this->getBundleSlotFromEvent($event);

        // TODO Get bundle from change set (in case it is null)

        $this->scheduleChildDataChangeEvent($bundleSlot->getBundle());
    }

    /**
     * Dispatches the child data change events.
     *
     * @param ProductInterface $bundle
     */
    private function scheduleChildDataChangeEvent(ProductInterface $bundle)
    {
        ProductTypes::assertBundled($bundle);

        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_DATA_CHANGE, $bundle);
    }

    /**
     * Returns the bundle slot from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleSlotInterface
     * @throws InvalidArgumentException
     */
    private function getBundleSlotFromEvent(ResourceEventInterface $event)
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
