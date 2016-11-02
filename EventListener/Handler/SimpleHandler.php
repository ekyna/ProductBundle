<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class SimpleHandler
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SimpleHandler extends AbstractHandler
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockUpdater;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param StockSubjectUpdaterInterface     $stockUpdater
     * @param ProductRepositoryInterface       $productRepository
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        ResourceEventDispatcherInterface $dispatcher,
        StockSubjectUpdaterInterface $stockUpdater,
        ProductRepositoryInterface $productRepository
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->dispatcher = $dispatcher;
        $this->stockUpdater = $stockUpdater;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if ($this->stockUpdater->update($product)) {
            $this->handleChildStockUpdate($product);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if ($this->persistenceHelper->isChanged($product, ['inStock', 'orderedStock', 'estimatedDateOfArrival'])) {
            if ($this->stockUpdater->updateStockState($product)) {
                $this->handleChildStockUpdate($product);

                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleStockUnitChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if (null !== $stockUnit = $event->getData('stock_unit')) {
            $changed = $this->updateStockByStockUnitChange($product, $stockUnit);
        } else {
            $changed = $this->updateStock($product);
        }

        if ($changed) {
            $this->handleChildStockUpdate($product);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleStockUnitRemoval(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if (null !== $stockUnit = $event->getData('stock_unit')) {
            $changed = $this->updateStockByStockUnitRemoval($product, $stockUnit);
        } else {
            $changed = $this->updateStock($product);
        }

        if ($changed) {
            $this->handleChildStockUpdate($product);
        }

        return $changed;
    }

    /**
     * Updates the stock data.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    private function updateStock(ProductInterface $product)
    {
        // In stock update
        $changed = $this->stockUpdater->updateInStock($product);

        // Ordered stock update
        $changed = $this->stockUpdater->updateOrderedStock($product) || $changed;

        // Estimated date of arrival update
        return $this->stockUpdater->updateEstimatedDateOfArrival($product) || $changed;
    }

    /**
     * Updates stock data regarding to the stock unit changes.
     *
     * @param ProductInterface   $product
     * @param StockUnitInterface $stockUnit
     *
     * @return bool
     */
    private function updateStockByStockUnitChange(ProductInterface $product, StockUnitInterface $stockUnit)
    {
        $cs = $this->persistenceHelper->getChangeSet($stockUnit);

        $changed = false;

        if (isset($cs['deliveredQuantity']) || isset($cs['shippedQuantity'])) {
            // Resolve delivered and shipped quantity changes
            $deliveredDelta = $deltaShipped = 0;
            if (isset($cs['deliveredQuantity'])) {
                $deliveredDelta = ((float)$cs['deliveredQuantity'][1]) - ((float)$cs['deliveredQuantity'][0]);
            }
            if (isset($cs['shippedQuantity'])) {
                $deltaShipped = ((float)$cs['shippedQuantity'][1]) - ((float)$cs['shippedQuantity'][0]);
            }

            // TODO really need tests T_T
            $changed = $this->stockUpdater->updateInStock($product, $deliveredDelta - $deltaShipped);
        }

        if (isset($cs['orderedQuantity'])) {
            // Resolve ordered quantity change
            $delta = ((float)$cs['orderedQuantity'][1]) - ((float)$cs['orderedQuantity'][0]);

            $changed = $this->stockUpdater->updateOrderedStock($product, $delta) || $changed;
        }

        if ($changed || isset($cs['estimatedDateOfArrival'])) {
            $date = $stockUnit->getState() !== StockUnitStates::STATE_CLOSED
                ? $stockUnit->getEstimatedDateOfArrival()
                : null;

            $changed = $this->stockUpdater->updateEstimatedDateOfArrival($product, $date) || $changed;
        }

        return $changed;
    }

    /**
     * Updates stock data regarding to the stock unit changes.
     *
     * @param ProductInterface   $product
     * @param StockUnitInterface $stockUnit
     *
     * @return bool
     */
    private function updateStockByStockUnitRemoval(ProductInterface $product, StockUnitInterface $stockUnit)
    {
        $changed = false;

        // We don't care about delivered and shipped stocks because the
        // stock unit removal is prevented if those stocks are not null.

        // Update ordered quantity
        if (0 < $stockUnit->getOrderedQuantity()) {
            $changed = $this->stockUpdater->updateOrderedStock($product, -$stockUnit->getOrderedQuantity());
        }

        // Update the estimated date of arrival
        $changed = $this->stockUpdater->updateEstimatedDateOfArrival($product) || $changed;

        return $changed;
    }

    /**
     * Handles the child stock update by dispatching an event to the parent(s).
     *
     * @param ProductInterface $child
     */
    private function handleChildStockUpdate(ProductInterface $child)
    {
        ProductTypes::assetChildType($child);

        if ($child->getType() === ProductTypes::TYPE_VARIANT) {
            if (!$variable = $child->getParent()) {
                throw new RuntimeException("Variant's parent must be set.");
            }
            $this->scheduleChildStockChangeEvent($variable);
        }

        $parents = $this->productRepository->findParentsByBundled($child);
        foreach ($parents as $parent) {
            $this->scheduleChildStockChangeEvent($parent);
        }
    }

    /**
     * Dispatches the parent (variable/bundle/configurable) "child stock change" event.
     *
     * @param ProductInterface $parent
     */
    private function scheduleChildStockChangeEvent(ProductInterface $parent)
    {
        ProductTypes::assetParentType($parent);

        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_STOCK_CHANGE, $parent);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return in_array($product->getType(), ProductTypes::getChildTypes());
    }
}
