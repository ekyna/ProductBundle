<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\PriceInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCacheClearer;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class PriceListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceListener
{
    public function __construct(
        private readonly PriceCacheClearer $clearer
    ) {
    }

    /**
     * Insert/Update/Delete event handler.
     */
    public function onChange(ResourceEventInterface $event): PriceInterface
    {
        $price = $this->getPriceFromEvent($event);

        $this->clearer->clearPriceCache($price->getProduct());

        return $price;
    }

    /**
     * Returns the price from the event.
     */
    protected function getPriceFromEvent(ResourceEventInterface $event): PriceInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof PriceInterface) {
            throw new UnexpectedTypeException($resource, PriceInterface::class);
        }

        return $resource;
    }
}
