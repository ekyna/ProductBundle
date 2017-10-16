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
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $bundleChoice = $this->getBundleChoiceFromEvent($event);

        $this->scheduleChildDataChangeEvent($bundleChoice->getSlot()->getBundle());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $bundleChoice = $this->getBundleChoiceFromEvent($event);

        if ($this->persistenceHelper->isChanged($bundleChoice, ['product', 'minQuantity'])) {
            $this->scheduleChildDataChangeEvent($bundleChoice->getSlot()->getBundle());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $bundleChoice = $this->getBundleChoiceFromEvent($event);

        // TODO Get bundle from change set (in case it is null)

        $this->scheduleChildDataChangeEvent($bundleChoice->getSlot()->getBundle());
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
     * Returns the bundle choice from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return BundleChoiceInterface
     * @throws InvalidArgumentException
     */
    private function getBundleChoiceFromEvent(ResourceEventInterface $event)
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
