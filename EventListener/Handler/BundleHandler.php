<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Updater\BundleUpdater;
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
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var BundleUpdater
     */
    private $bundleUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param PriceCalculator            $priceCalculator
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        PriceCalculator $priceCalculator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->priceCalculator = $priceCalculator;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $this->checkQuantities($bundle);

        $changed = $this->getBundleUpdater()->updateStock($bundle);

        $changed |= $this->updatePrice($bundle);

        $changed |= $this->ensureInheritedStockMode($bundle);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        $this->checkQuantities($bundle);

        $changed = $this->ensureInheritedStockMode($bundle);

        $events = [];

        // TODO Weight

        if ($this->updatePrice($bundle)) {
            $events[] = ProductEvents::CHILD_DATA_CHANGE;
            $changed = true;
        }

        if ($this->getBundleUpdater()->updateStock($bundle)) {
            $events[] = ProductEvents::CHILD_STOCK_CHANGE;
            $changed = true;
        }

        if (!empty($events)) {
            $this->scheduleChildChangeEvents($bundle, $events);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleChildDataChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        // TODO Weight

        if ($this->updatePrice($bundle)) {
            $this->scheduleChildChangeEvents($bundle, [ProductEvents::CHILD_DATA_CHANGE]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $bundle = $this->getProductFromEvent($event, ProductTypes::TYPE_BUNDLE);

        if ($this->getBundleUpdater()->updateStock($bundle)) {
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
     * Updates the bundle price.
     *
     * @param ProductInterface $bundle
     *
     * @return bool
     */
    protected function updatePrice(ProductInterface $bundle)
    {
        ProductTypes::assertBundle($bundle);

        $netPrice = $this->priceCalculator->calculateBundleTotalPrice($bundle);

        if ($netPrice !== $bundle->getNetPrice()) {
            $bundle->setNetPrice($netPrice);

            return true;
        }

        return false;
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

        return $this->bundleUpdater = new BundleUpdater();
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
