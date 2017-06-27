<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
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

        return $this->stockUpdater->update($product);
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $changed = false;
        $childEvents = [];

        if ($this->persistenceHelper->isChanged($product, ['inStock', 'virtualStock', 'estimatedDateOfArrival'])) {
            if ($this->stockUpdater->updateStockState($product)) {
                $childEvents[] = ProductEvents::CHILD_STOCK_CHANGE;

                $changed = true;
            }
        }

        if ($this->persistenceHelper->isChanged($product, ['netPrice', 'weight'])) {
            $childEvents[] = ProductEvents::CHILD_DATA_CHANGE;
        }

        if (!empty($childEvents)) {
            $this->scheduleChildChangeEvents($product, $childEvents);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleStockUnitChange(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if (null !== $stockUnit = $event->getStockUnit()) {
            $changed = $this->stockUpdater->updateFromStockUnitChange($product, $stockUnit);
        } else {
            $changed = $this->updateStock($product);
        }

        if ($changed) {
            $this->scheduleChildChangeEvents($product, [ProductEvents::CHILD_STOCK_CHANGE]);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleStockUnitRemoval(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if (null !== $stockUnit = $event->getStockUnit()) {
            $changed = $this->stockUpdater->updateFromStockUnitRemoval($product, $stockUnit);
        } else {
            $changed = $this->updateStock($product);
        }

        if ($changed) {
            $this->scheduleChildChangeEvents($product, [ProductEvents::CHILD_STOCK_CHANGE]);
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return in_array($product->getType(), ProductTypes::getChildTypes());
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

        // Virtual stock update
        $changed |= $this->stockUpdater->updateVirtualStock($product);

        // Estimated date of arrival update
        $changed |= $this->stockUpdater->updateEstimatedDateOfArrival($product);

        return $changed;

        // TODO Check that stock state update is ALWAYS done by the handleUpdate method.
        // Stock state
        //return $this->stockUpdater->updateStockState($product) || $changed;
    }

    /**
     * Dispatches the child change events.
     *
     * @param ProductInterface $child
     * @param array            $events
     */
    protected function scheduleChildChangeEvents(ProductInterface $child, array $events)
    {
        ProductTypes::assertChildType($child);

        if ($child->getType() === ProductTypes::TYPE_VARIANT) {
            if (!$variable = $child->getParent()) {
                throw new RuntimeException("Variant's parent must be set.");
            }

            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $variable);
            }
        }

        $parents = $this->productRepository->findParentsByBundled($child);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $parent);
            }
        }
    }
}
