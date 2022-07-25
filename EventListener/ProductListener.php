<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\EventListener\Handler\HandlerInterface;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class ProductListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductListener
{
    public function __construct(
        protected readonly PersistenceHelperInterface   $persistenceHelper,
        protected readonly Handler\HandlerRegistry      $handlerRegistry,
        protected readonly GeneratorInterface           $referenceGenerator,
        protected readonly OfferInvalidator             $offerInvalidator,
        protected readonly PriceInvalidator             $priceInvalidator,
        protected readonly StockSubjectUpdaterInterface $stockSubjectUpdater
    ) {
    }

    /**
     * Pre create event handler.
     */
    public function onPreCreate(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            // Pre load the variants for position indexation
            $product->getParent()->getVariants()->toArray();
        }
    }

    /**
     * Pre update event handler.
     */
    public function onPreUpdate(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            // Pre load the variants for position indexation
            $product->getParent()->getVariants()->toArray();
        }
    }

    /**
     * Pre delete event handler.
     */
    public function onPreDelete(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            // Pre load the variants for position indexation
            $product->getParent()->getVariants()->toArray();
        }
    }

    /**
     * Insert event handler.
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->executeHandlers($event, HandlerInterface::INSERT);

        $changed = $this->generateReference($product) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Update event handler.
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->executeHandlers($event, HandlerInterface::UPDATE);

        $changed = $this->generateReference($product) || $changed;

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }

        // Schedule offers update if needed
        if ($this->persistenceHelper->isChanged($product, ['netPrice', 'brand'])) {
            $this->offerInvalidator->invalidateByProduct($product);
        }
    }

    /**
     * Delete event handler.
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $this->executeHandlers($event, HandlerInterface::DELETE);
    }

    /**
     * Stock unit change event handler.
     */
    public function onStockUnitChange(SubjectStockUnitEvent $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::STOCK_UNIT_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Stock unit delete event handler.
     */
    public function onStockUnitRemoval(SubjectStockUnitEvent $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::STOCK_UNIT_REMOVAL, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Child price change event handler.
     */
    public function onChildPriceChange(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::CHILD_PRICE_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Child availability change event handler.
     */
    public function onChildAvailabilityChange(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::CHILD_AVAILABILITY_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Child stock change event handler.
     */
    public function onChildStockChange(ResourceEventInterface $event): void
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::CHILD_STOCK_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Execute the event handlers method regarding the product type,
     * and returns whether or the product has been changed.
     */
    protected function executeHandlers(ResourceEventInterface $event, string $method, bool $skipDeleted = false): bool
    {
        $product = $this->getProductFromEvent($event);

        if ($skipDeleted && $this->persistenceHelper->isScheduledForRemove($product)) {
            return false;
        }

        $changed = false;

        $handlers = $this->handlerRegistry->getHandlers($product);
        foreach ($handlers as $handler) {
            $changed = call_user_func([$handler, $method], $event) || $changed;
        }

        return $changed;
    }

    /**
     * Generates the product reference if it is empty.
     */
    protected function generateReference(ProductInterface $product): bool
    {
        if (!empty($product->getReference())) {
            return false;
        }

        $product->setReference($this->referenceGenerator->generate($product));

        return true;
    }

    /**
     * Returns the product from the event.
     */
    protected function getProductFromEvent(ResourceEventInterface $event): ProductInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new UnexpectedTypeException($resource, ProductInterface::class);
        }

        return $resource;
    }
}
