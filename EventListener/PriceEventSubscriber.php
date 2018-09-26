<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\Common\Cache\MultiOperationCache;
use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Event\PriceEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PriceEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var array
     */
    private $cacheIds = [];


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Insert/Update/Delete event handler.
     *
     * @param ResourceEvent $event
     */
    public function onChange(ResourceEvent $event)
    {
        $price = $this->getPriceFromEvent($event);

        $this->cacheIds[] = $price->getCacheId();
    }

    /**
     * Kernel/Console terminate event handler.
     */
    public function onTerminate()
    {
        if (empty($this->cacheIds)) {
            return;
        }

        $cache = $this
            ->persistenceHelper
            ->getManager()
            ->getConfiguration()
            ->getResultCacheImpl();

        if (!$cache) {
            return;
        }

        if ($cache instanceof MultiOperationCache) {
            $cache->deleteMultiple($this->cacheIds);
        } else {
            foreach ($this->cacheIds as $childId) {
                $cache->delete($childId);
            }
        }

        $this->cacheIds = [];
    }

    /**
     * Returns the price from the event.
     *
     * @param ResourceEvent $event
     *
     * @return Price
     */
    private function getPriceFromEvent(ResourceEvent $event)
    {
        $offer = $event->getResource();

        if (!$offer instanceof Price) {
            throw new InvalidArgumentException("Expected instance of " . Price::class);
        }

        return $offer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PriceEvents::INSERT => ['onChange', 0],
            PriceEvents::UPDATE => ['onChange', 0],
            PriceEvents::DELETE => ['onChange', 0],
        ];
    }
}
