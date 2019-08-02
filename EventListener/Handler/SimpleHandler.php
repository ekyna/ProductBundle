<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
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
     * @var PriceCalculator
     */
    private $calculator;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $stockUpdater;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PriceInvalidator
     */
    private $priceInvalidator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface       $persistenceHelper
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param PriceCalculator                  $calculator
     * @param StockSubjectUpdaterInterface     $stockUpdater
     * @param ProductRepositoryInterface       $productRepository
     * @param PriceInvalidator                 $priceInvalidator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        ResourceEventDispatcherInterface $dispatcher,
        PriceCalculator $calculator,
        StockSubjectUpdaterInterface $stockUpdater,
        ProductRepositoryInterface $productRepository,
        PriceInvalidator $priceInvalidator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->dispatcher = $dispatcher;
        $this->calculator = $calculator;
        $this->stockUpdater = $stockUpdater;
        $this->productRepository = $productRepository;
        $this->priceInvalidator = $priceInvalidator;
    }

    // TODO Deal with required option group stocks ?

    /**
     * @inheritdoc
     */
    public function handleInsert(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $changed = $this->stockUpdater->update($product);

        $changed |= $this->updateMinPrice($product);

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function handleUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $changed = false;
        $childEvents = [];

        $stockProperties = ['stockMode', 'inStock', 'availableStock', 'virtualStock', 'estimatedDateOfArrival'];
        if ($this->persistenceHelper->isChanged($product, $stockProperties)) {
            $changed |= $this->stockUpdater->update($product);

            $childEvents[] = ProductEvents::CHILD_STOCK_CHANGE;
        } elseif ($this->persistenceHelper->isChanged($product, 'stockState')) {
            $childEvents[] = ProductEvents::CHILD_STOCK_CHANGE;
        }

        if ($this->persistenceHelper->isChanged($product, 'netPrice')) {
            $changed |= $this->updateMinPrice($product);

            $this->priceInvalidator->invalidateByProduct($product);

            $childEvents[] = ProductEvents::CHILD_PRICE_CHANGE;
        }

        $availabilityProperties = ['visible', 'quoteOnly', 'endOfLife'];
        if ($this->persistenceHelper->isChanged($product, $availabilityProperties)) {
            $childEvents[] = ProductEvents::CHILD_AVAILABILITY_CHANGE;

            if ($this->persistenceHelper->isChanged($product, 'visible')) {
                $this->priceInvalidator->invalidateParentsPrices($product);
            }
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

        $changed = $this->stockUpdater->update($product);

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

        if ($this->stockUpdater->update($product)) {
            $this->scheduleChildChangeEvents($product, [ProductEvents::CHILD_STOCK_CHANGE]);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handleChildStockChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        if ($this->stockUpdater->update($product)) {
            $this->scheduleChildChangeEvents($product, [ProductEvents::CHILD_STOCK_CHANGE]);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function handleChildPriceChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event, ProductTypes::getChildTypes());

        $this->priceInvalidator->invalidateParentsPrices($product);

        return $this->updateMinPrice($product);
    }

    /**
     * @inheritdoc
     */
    public function supports(ProductInterface $product)
    {
        return in_array($product->getType(), ProductTypes::getChildTypes());
    }

    /**
     * Updates the product minimum price.
     *
     * @param ProductInterface $product
     *
     * @return bool Whether the minimum price has been changed.
     */
    protected function updateMinPrice(ProductInterface $product)
    {
        $minPrice = $this->calculator->calculateProductMinPrice($product);
        if (0 !== bccomp($product->getMinPrice(), $minPrice)) {
            $product->setMinPrice($minPrice);

            return true;
        }

        return true;
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
            if (null === $variable = $child->getParent()) {
                throw new RuntimeException("Variant's parent must be set.");
            }

            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $variable);
            }
        }

        $parents = $this->productRepository->findParentsByOptionProduct($child, true);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $parent);
            }
        }

        $parents = $this->productRepository->findParentsByBundled($child);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $parent);
            }
        }

        $parents = $this->productRepository->findParentsByComponent($child);
        foreach ($parents as $parent) {
            foreach ($events as $event) {
                $this->persistenceHelper->scheduleEvent($event, $parent);
            }
        }
    }
}
