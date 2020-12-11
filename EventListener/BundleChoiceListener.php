<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\BundleChoiceEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BundleChoiceListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceListener implements EventSubscriberInterface
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
     * @return BundleChoiceInterface
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $choice = $this->getBundleChoiceFromEvent($event);

        $this->scheduleChildPriceChangeEvent($choice->getSlot()->getBundle());

        return $choice;
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleChoiceInterface
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $choice = $this->getBundleChoiceFromEvent($event);

        $properties = ['product', 'minQuantity', 'netPrice', 'excludedOptionGroups', 'hidden'];
        if ($this->persistenceHelper->isChanged($choice, $properties)) {
            $this->scheduleChildPriceChangeEvent($choice->getSlot()->getBundle());
        }

        return $choice;
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleChoiceInterface
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $choice = $this->getBundleChoiceFromEvent($event);

        // Get bundle from change set if null
        if (null === $slot = $choice->getSlot()) {
            $slot = $this->persistenceHelper->getChangeSet($choice, 'slot')[0];
        }
        if (null === $bundle = $slot->getBundle()) {
            $bundle = $this->persistenceHelper->getChangeSet($slot, 'bundle')[0];
        }

        $this->scheduleChildPriceChangeEvent($bundle);

        return $choice;
    }

    /**
     * Dispatches the child price change events.
     *
     * @param ProductInterface $bundle
     */
    private function scheduleChildPriceChangeEvent(ProductInterface $bundle)
    {
        if (!ProductTypes::isBundledType($bundle)) {
            return;
        }

        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_PRICE_CHANGE, $bundle);
    }

    /**
     * Returns the bundle choice from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleChoiceInterface
     * @throws InvalidArgumentException
     */
    protected function getBundleChoiceFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof BundleChoiceInterface) {
            throw new InvalidArgumentException('Expected instance of ' . BundleChoiceInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            BundleChoiceEvents::INSERT => ['onInsert', 0],
            BundleChoiceEvents::UPDATE => ['onUpdate', 0],
            BundleChoiceEvents::DELETE => ['onDelete', 0],
        ];
    }
}
