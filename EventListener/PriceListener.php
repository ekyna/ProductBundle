<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\Common\Cache\MultiOperationCache;
use Ekyna\Bundle\ProductBundle\Entity\Price;
use Ekyna\Bundle\ProductBundle\Event\PriceEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Service\Pricing\CacheUtil;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PriceListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceListener implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var CustomerGroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var CountryRepositoryInterface
     */
    protected $countryRepository;

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
     *
     * @return Price
     */
    public function onChange(ResourceEvent $event)
    {
        $price = $this->getPriceFromEvent($event);

        $groups = $price->getGroup()
            ? [$price->getGroup()->getId()]
            : $this->customerGroupRepository->getIdentifiers();

        $countries = $price->getCountry()
            ? [$price->getCountry()->getId()]
            : $this->countryRepository->getIdentifiers(true);

        $product = $price->getProduct()->getId();

        foreach ($groups as $group) {
            foreach ($countries as $country) {
                CacheUtil::addKeyToList($this->cacheIds, CacheUtil::buildPriceKeyByIds($product, $group, $country));
            }
        }

        return $price;
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
    protected function getPriceFromEvent(ResourceEvent $event)
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
