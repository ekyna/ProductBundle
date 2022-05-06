<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Decimal\Decimal;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\MultiOperationCache;
use Ekyna\Bundle\ProductBundle\Event\OfferEvents;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\OfferInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\CacheUtil;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * OfferListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OfferListener implements EventSubscriberInterface
{
    protected PersistenceHelperInterface       $persistenceHelper;
    protected CustomerGroupRepositoryInterface $customerGroupRepository;
    protected CountryRepositoryInterface       $countryRepository;
    private ?CacheProvider                     $resultCache;
    private array                              $productIds = [];
    private array                              $cacheIds   = [];

    public function __construct(
        PersistenceHelperInterface       $persistenceHelper,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CountryRepositoryInterface       $countryRepository,
        CacheProvider                    $resultCache = null
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->countryRepository = $countryRepository;
        $this->resultCache = $resultCache;
    }

    /**
     * Insert/Update/Delete event handler.
     */
    public function onChange(ResourceEventInterface $event): OfferInterface
    {
        $offer = $this->getOfferFromEvent($event);

        $id = $offer->getProduct()->getId();

        if (!in_array($id, $this->productIds, true)) {
            $this->productIds[] = $id;
        }

        return $offer;
    }

    /**
     * Kernel/Console terminate event handler.
     */
    public function onTerminate(): void
    {
        if (null === $this->resultCache) {
            return;
        }

        if (empty($this->productIds)) {
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
                        CacheUtil::buildOfferKeyByIds($product, $group, $country, new Decimal(1), false)
                    );
                }
            }
        }

        if ($this->resultCache instanceof MultiOperationCache) {
            $this->resultCache->deleteMultiple($this->cacheIds);
        } else {
            foreach ($this->cacheIds as $childId) {
                $this->resultCache->delete($childId);
            }
        }

        $this->cacheIds = [];
        $this->productIds = [];
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

    public static function getSubscribedEvents(): array
    {
        return [
            OfferEvents::INSERT => ['onChange', 0],
            OfferEvents::UPDATE => ['onChange', 0],
            OfferEvents::DELETE => ['onChange', 0],
        ];
    }
}
