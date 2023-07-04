<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\OfferInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCacheClearer;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * OfferListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferListener
{
    public function __construct(
        private readonly PriceCacheClearer $clearer
    ) {
    }

    /**
     * Insert/Update/Delete event handler.
     */
    public function onChange(ResourceEventInterface $event): OfferInterface
    {
        $offer = $this->getOfferFromEvent($event);

        $this->clearer->clearOfferCache($offer->getProduct());

        return $offer;
    }

    /**
     * Returns the offer from the event.
     */
    protected function getOfferFromEvent(ResourceEventInterface $event): OfferInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof OfferInterface) {
            throw new UnexpectedTypeException($resource, OfferInterface::class);
        }

        return $resource;
    }
}
