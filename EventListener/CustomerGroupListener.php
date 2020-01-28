<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Commerce\Customer\Event\CustomerGroupEvents;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerGroupListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupListener implements EventSubscriberInterface
{
    /**
     * @var PriceInvalidator
     */
    private $priceInvalidator;


    /**
     * Constructor.
     *
     * @param PriceInvalidator $priceInvalidator
     */
    public function __construct(PriceInvalidator $priceInvalidator)
    {
        $this->priceInvalidator = $priceInvalidator;
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $group = $this->getCustomerGroupFromEvent($event);

        $this->priceInvalidator->invalidateByCustomerGroup($group);
    }

    /**
     * Returns the customer group from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return CustomerGroupInterface
     * @throws InvalidArgumentException
     */
    protected function getCustomerGroupFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerGroupInterface) {
            throw new InvalidArgumentException('Expected instance of ' . CustomerGroupInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerGroupEvents::DELETE => ['onDelete', -10],
        ];
    }
}
