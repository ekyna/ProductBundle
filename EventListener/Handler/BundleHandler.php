<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Updater\BundleUpdater;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class BundleHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleHandler extends AbstractHandler
{
    private ?BundleUpdater $bundleUpdater = null;

    public function __construct(
        private readonly PersistenceHelperInterface   $persistenceHelper,
        private readonly ProductRepositoryInterface   $productRepository,
        private readonly PriceCalculator              $priceCalculator,
        private readonly OfferInvalidator             $offerInvalidator,
        private readonly PriceInvalidator             $priceInvalidator,
        private readonly StockSubjectUpdaterInterface $stockUpdater
    ) {
    }

    public function handleInsert(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $this->checkQuantities($bundle);

        $changed = $this->stockUpdater->update($bundle);

        $changed = $this->updateNetPrice($bundle) || $changed;

        return $this->updateMinPrice($bundle) || $changed;
    }

    public function handleUpdate(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $this->checkQuantities($bundle);

        $events = [];
        $changed = false;

        // TODO remove : stock should only change from children
        if ($this->stockUpdater->update($bundle)) {
            $events[] = ProductEvents::CHILD_STOCK_CHANGE;
            $changed = true;
        }
        if ($this->updateNetPrice($bundle)) {
            $events[] = ProductEvents::CHILD_PRICE_CHANGE;
            $changed = true;
        }

        $changed = $this->updateMinPrice($bundle) || $changed;

        if (!empty($events)) {
            $this->scheduleChildChangeEvents($bundle, $events);
        }

        return $changed;
    }

    public function handleChildPriceChange(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $changed = false;

        if ($this->updateNetPrice($bundle)) {
            $this->scheduleChildChangeEvents($bundle, [ProductEvents::CHILD_PRICE_CHANGE]);
            $changed = true;
        }

        return $this->updateMinPrice($bundle) || $changed;
    }

    protected function updateNetPrice(ProductInterface $product): bool
    {
        if (!$this->getBundleUpdater()->updateNetPrice($product)) {
            return false;
        }

        $this->offerInvalidator->invalidateByProduct($product);

        return true;
    }

    protected function updateMinPrice(ProductInterface $product): bool
    {
        if (!$this->getBundleUpdater()->updateMinPrice($product)) {
            return false;
        }

        $this->priceInvalidator->invalidateByProduct($product);

        return true;
    }

    public function handleChildAvailabilityChange(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        if ($this->getBundleUpdater()->updateReleasedAt($bundle)) {
            $this->scheduleChildChangeEvents($bundle, [ProductEvents::CHILD_AVAILABILITY_CHANGE]);

            return true;
        }

        return false;
    }

    public function handleChildStockChange(ResourceEventInterface $event): bool
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        if ($this->stockUpdater->update($bundle)) {
            $this->scheduleChildChangeEvents($bundle, [ProductEvents::CHILD_STOCK_CHANGE]);

            return true;
        }

        return false;
    }

    public function supports(ProductInterface $product): bool
    {
        return $product->getType() === ProductTypes::TYPE_BUNDLE;
    }

    /**
     * Check the bundle slots choices quantities.
     */
    protected function checkQuantities(ProductInterface $bundle): void
    {
        ProductTypes::assertBundle($bundle);

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();

            if (!$choice->getMaxQuantity()->equals($choice->getMinQuantity())) {
                $choice->setMaxQuantity($choice->getMinQuantity());

                $this->persistenceHelper->persistAndRecompute($choice, false);
            }
        }
    }

    protected function getBundleUpdater(): BundleUpdater
    {
        if (null !== $this->bundleUpdater) {
            return $this->bundleUpdater;
        }

        return $this->bundleUpdater = new BundleUpdater($this->priceCalculator);
    }

    /**
     * Dispatches the child change events.
     */
    protected function scheduleChildChangeEvents(ProductInterface $bundle, array $events): void
    {
        ProductTypes::assertBundle($bundle);

        $parents = $this->productRepository->findParentsByBundled($bundle);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($parent, $event);
            }
        }
    }
}
