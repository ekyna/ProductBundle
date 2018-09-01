<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\Common\Cache\MultiOperationCache;
use Ekyna\Bundle\ProductBundle\Entity\Offer;
use Ekyna\Bundle\ProductBundle\Event\OfferEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
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
    private $childrenIds = [];

    /**
     * @var array
     */
    private $parentIds = [];

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

        $this->invalidateOffer($offer);
    }

    /**
     * Kernel/Console terminate event handler.
     */
    public function onTerminate()
    {
        if (empty($this->childrenIds) && empty($this->parentIds)) {
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

        $groupIds = $this->customerGroupRepository->getIdentifiers();
        $countryIds = $this->countryRepository->getIdentifiers(true);

        array_unshift($groupIds, 0);
        array_unshift($countryIds, 0);

        foreach ($this->childrenIds as $productId) {
            foreach ($groupIds as $groupId) {
                foreach ($countryIds as $countryId) {
                    $this->addId(
                        $this->cacheIds,
                        Offer::buildCacheIdByIds($productId, $groupId, $countryId)
                    );
                    $this->addId(
                        $this->cacheIds,
                        Offer::buildCacheIdByIds($productId, $groupId, $countryId, 1, false)
                    );
                }
            }
        }

        foreach ($this->parentIds as $productId) {
            foreach ($groupIds as $groupId) {
                foreach ($countryIds as $countryId) {
                    $this->addId(
                        $this->cacheIds,
                        Offer::buildCacheIdByIds($productId, $groupId, $countryId)
                    );
                    $this->addId(
                        $this->cacheIds,
                        Offer::buildCacheIdByIds($productId, $groupId, $countryId, 1, false)
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
    }

    /**
     * Invalidate the result cache for the given offer.
     *
     * @param Offer $offer
     */
    private function invalidateOffer(Offer $offer)
    {
        $product = $offer->getProduct();
        if (ProductTypes::isChildType($product->getType())) {
            $this->addId($this->childrenIds, $product->getId());
        } else {
            $this->addId($this->parentIds, $product->getId());
        }
    }

    /**
     * Adds the product id to the children list.
     *
     * @param array  $list
     * @param string $id
     */
    private function addId(&$list, $id)
    {
        if (!in_array($id, $list, true)) {
            $list[] = $id;
        }
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
