<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\Common\Cache\MultiOperationCache;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Event\OfferEvents;
use Ekyna\Bundle\ProductBundle\Service\Pricing\CacheUtil;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * OfferEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var CustomerGroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * @var array
     */
    private $cacheIds = [];


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param CountryRepositoryInterface       $countryRepository
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CountryRepositoryInterface $countryRepository
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->countryRepository = $countryRepository;
    }

    /**
     * Insert/Update/Delete event handler.
     *
     * @param ResourceEvent $event
     */
    public function onChange(ResourceEvent $event)
    {
        $offer = $this->getOfferFromEvent($event);

        $id = (int)$offer->getProduct()->getId();

        if (!in_array($id, $this->productIds, true)) {
            $this->productIds[] = $id;
        }
    }

    /**
     * Kernel/Console terminate event handler.
     */
    public function onTerminate()
    {
        if (empty($this->productIds)) {
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

        $groups = $this->customerGroupRepository->getIdentifiers();
        $countries = $this->countryRepository->getIdentifiers(true);

        foreach ($this->productIds as $product) {
            foreach ($groups as $group) {
                foreach ($countries as $country) {
                    CacheUtil::addKeyToList(
                        $this->cacheIds,
                        CacheUtil::buildOfferKeyByIds($product, $group, $country)
                    );
                    CacheUtil::addKeyToList(
                        $this->cacheIds,
                        CacheUtil::buildOfferKeyByIds($product, $group, $country, 1, false)
                    );
                }
            }
        }

        if ($cache instanceof MultiOperationCache) {
            $cache->deleteMultiple($this->cacheIds);
        } else {
            foreach ($this->cacheIds as $childId) {
                $cache->delete($childId);
            }
        }

        $this->cacheIds = [];
        $this->productIds = [];
    }

    /**
     * Returns the offer from the event.
     *
     * @param ResourceEvent $event
     *
     * @return Offer
     */
    private function getOfferFromEvent(ResourceEvent $event)
    {
        $offer = $event->getResource();

        if (!$offer instanceof Offer) {
            throw new InvalidArgumentException("Expected instance of " . Offer::class);
        }

        return $offer;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OfferEvents::INSERT => ['onChange', 0],
            OfferEvents::UPDATE => ['onChange', 0],
            OfferEvents::DELETE => ['onChange', 0],
        ];
    }
}
