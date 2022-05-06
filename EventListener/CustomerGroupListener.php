<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Commerce\Customer\Event\CustomerGroupEvents;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerGroupListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupListener implements EventSubscriberInterface
{
    private OfferInvalidator $offerInvalidator;
    private PriceInvalidator $priceInvalidator;

    public function __construct(OfferInvalidator $offerInvalidator, PriceInvalidator $priceInvalidator)
    {
        $this->offerInvalidator = $offerInvalidator;
        $this->priceInvalidator = $priceInvalidator;
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $group = $this->getCustomerGroupFromEvent($event);

        $this->offerInvalidator->invalidateByCustomerGroup($group);
        $this->priceInvalidator->invalidateByCustomerGroup($group);
    }

    /**
     * Returns the customer group from the event.
     */
    protected function getCustomerGroupFromEvent(ResourceEventInterface $event): CustomerGroupInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof CustomerGroupInterface) {
            throw new UnexpectedTypeException($resource, CustomerGroupInterface::class);
        }

        return $resource;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerGroupEvents::DELETE => ['onDelete', -10],
        ];
    }
}
