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

        if ($this->persistenceHelper->isChanged($product, ['inStock', 'virtualStock', 'estimatedDateOfArrival'])) {
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
    public function handleStockUnitChange(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if (null !== $stockUnit = $event->getStockUnit()) {
            $changed = $this->stockUpdater->updateFromStockUnitChange($product, $stockUnit);
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
    public function handleStockUnitRemoval(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if (null !== $stockUnit = $event->getStockUnit()) {
            $changed = $this->stockUpdater->updateFromStockUnitRemoval($product, $stockUnit);
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

        // TODO Check that stock state update is ALWAYS done by the handUpdate method.
        // Stock state
        //return $this->stockUpdater->updateStockState($product) || $changed;
    }

    /**
     * Handles the child stock update by dispatching an event to the parent(s).
     *
     * @param ProductInterface $child
     */
    private function handleChildStockUpdate(ProductInterface $child)
    {
        ProductTypes::assertChildType($child);

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
        ProductTypes::assertParentType($parent);

        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_STOCK_CHANGE, $parent);
    }
}
