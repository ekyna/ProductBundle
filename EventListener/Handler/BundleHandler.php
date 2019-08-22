<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
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
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PriceInvalidator
     */
    private $priceInvalidator;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockUpdater;

    /**
     * @var BundleUpdater
     */
    private $bundleUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface   $persistenceHelper
     * @param ProductRepositoryInterface   $productRepository
     * @param PriceCalculator              $priceCalculator
     * @param PriceInvalidator             $priceInvalidator
     * @param StockSubjectUpdaterInterface $stockUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        ProductRepositoryInterface $productRepository,
        PriceCalculator $priceCalculator,
        PriceInvalidator $priceInvalidator,
        StockSubjectUpdaterInterface $stockUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->productRepository = $productRepository;
        $this->priceCalculator = $priceCalculator;
        $this->priceInvalidator = $priceInvalidator;
        $this->stockUpdater = $stockUpdater;
    }

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $this->checkQuantities($bundle);

        $updater = $this->getBundleUpdater();

        $changed = $this->stockUpdater->update($bundle);

        $changed |= $updater->updateNetPrice($bundle);

        $changed |= $updater->updateMinPrice($bundle);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $this->checkQuantities($bundle);

        $updater = $this->getBundleUpdater();

        $events = [];
        $changed = false;

        // TODO remove : stock should only change from children
        if ($this->stockUpdater->update($bundle)) {
            $events[] = ProductEvents::CHILD_STOCK_CHANGE;
            $changed = true;
        }
        if ($updater->updateNetPrice($bundle)) {
            $events[] = ProductEvents::CHILD_PRICE_CHANGE;
            $changed = true;
        }

        $changed |= $updater->updateMinPrice($bundle);

        if (!empty($events)) {
            $this->scheduleChildChangeEvents($bundle, $events);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildPriceChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $updater = $this->getBundleUpdater();

        $this->priceInvalidator->invalidateByProduct($bundle);

        $changed = false;

        if ($updater->updateNetPrice($bundle)) {
            $this->scheduleChildChangeEvents($bundle, [ProductEvents::CHILD_PRICE_CHANGE]);
            $changed = true;
        }

        if ($updater->updateMinPrice($bundle)) {
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        if ($this->stockUpdater->update($bundle)) {
            $this->scheduleChildChangeEvents($bundle, [ProductEvents::CHILD_STOCK_CHANGE]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return $product->getType() === ProductTypes::TYPE_BUNDLE;
    }

    /**
     * Check the bundle slots choices quantities.
     *
     * @param ProductInterface $bundle
     */
    protected function checkQuantities(ProductInterface $bundle)
    {
        ProductTypes::assertBundle($bundle);

        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();

            if ($choice->getMaxQuantity() !== $choice->getMinQuantity()) {
                $choice->setMaxQuantity($choice->getMinQuantity());

                $this->persistenceHelper->persistAndRecompute($choice, false);
            }
        }
    }

    /**
     * Returns the bundle updater.
     *
     * @return BundleUpdater
     */
    protected function getBundleUpdater()
    {
        if (null !== $this->bundleUpdater) {
            return $this->bundleUpdater;
        }

        return $this->bundleUpdater = new BundleUpdater($this->priceCalculator);
    }

    /**
     * Dispatches the child change events.
     *
     * @param ProductInterface $bundle
     * @param array            $events
     */
    protected function scheduleChildChangeEvents(ProductInterface $bundle, array $events)
    {
        ProductTypes::assertBundle($bundle);

        $parents = $this->productRepository->findParentsByBundled($bundle);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $parent);
            }
        }
    }
}
