<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\OptionGroupEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OptionGroupListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupListener implements EventSubscriberInterface
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
        $group = $this->getOptionGroupFromEvent($event);

        $this->scheduleChildPriceChangeEvent($group->getProduct());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $group = $this->getOptionGroupFromEvent($event);

        if ($this->persistenceHelper->isChanged($group, 'required')) {
            $this->scheduleChildPriceChangeEvent($group->getProduct());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $group = $this->getOptionGroupFromEvent($event);

        if (null === $product = $group->getProduct()) {
            $product = $this->persistenceHelper->getChangeSet($group, 'product')[0];
        }
        if (null !== $product) {
            $this->scheduleChildPriceChangeEvent($product);
        }
    }

    /**
     * Dispatches the child price change events.
     *
     * @param ProductInterface $product
     */
    protected function scheduleChildPriceChangeEvent(ProductInterface $product)
    {
        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_PRICE_CHANGE, $product);
    }

    /**
     * Returns the option group from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return OptionGroupInterface
     * @throws InvalidArgumentException
     */
    protected function getOptionGroupFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OptionGroupInterface) {
            throw new InvalidArgumentException('Expected instance of ' . OptionGroupInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OptionGroupEvents::INSERT => ['onInsert', 0],
            OptionGroupEvents::UPDATE => ['onUpdate', 0],
            OptionGroupEvents::DELETE => ['onDelete', 0],
        ];
    }
}
