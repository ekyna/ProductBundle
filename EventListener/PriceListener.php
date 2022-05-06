<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\MultiOperationCache;
use Ekyna\Bundle\ProductBundle\Event\PriceEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\PriceInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\CacheUtil;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PriceListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceListener implements EventSubscriberInterface
{
    protected PersistenceHelperInterface       $persistenceHelper;
    protected CustomerGroupRepositoryInterface $customerGroupRepository;
    protected CountryRepositoryInterface       $countryRepository;
    private ?CacheProvider                     $resultCache;
    private array                              $cacheIds = [];

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
    public function onChange(ResourceEventInterface $event): PriceInterface
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
    public function onTerminate(): void
    {
        if (null === $this->resultCache) {
            return;
        }

        if (empty($this->cacheIds)) {
            return;
        }

        if ($this->resultCache instanceof MultiOperationCache) {
            $this->resultCache->deleteMultiple($this->cacheIds);
        } else {
            foreach ($this->cacheIds as $childId) {
                $this->resultCache->delete($childId);
            }
        }

        $this->cacheIds = [];
    }

    /**
     * Returns the price from the event.
     */
    protected function getPriceFromEvent(ResourceEventInterface $event): PriceInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof PriceInterface) {
            throw new InvalidArgumentException($resource, PriceInterface::class);
        }

        return $resource;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PriceEvents::INSERT => ['onChange', 0],
            PriceEvents::UPDATE => ['onChange', 0],
            PriceEvents::DELETE => ['onChange', 0],
        ];
    }
}
