<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\EventListener\Handler\HandlerInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Generator\ReferenceGeneratorInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Resource\Event\QueueEvents;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var Handler\HandlerRegistry
     */
    private $handlerRegistry;

    /**
     * @var ReferenceGeneratorInterface
     */
    private $referenceGenerator;

    /**
     * @var OfferInvalidator
     */
    private $offerInvalidator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface  $persistenceHelper
     * @param Handler\HandlerRegistry     $registry
     * @param ReferenceGeneratorInterface $referenceGenerator
     * @param OfferInvalidator            $offerInvalidator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        Handler\HandlerRegistry $registry,
        ReferenceGeneratorInterface $referenceGenerator,
        OfferInvalidator $offerInvalidator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->handlerRegistry = $registry;
        $this->referenceGenerator = $referenceGenerator;
        $this->offerInvalidator = $offerInvalidator;
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function preCreate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            // Pre load the variants for position indexation
            $product->getParent()->getVariants()->toArray();
        }
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function preUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            // Pre load the variants for position indexation
            $product->getParent()->getVariants()->toArray();
        }
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function preDelete(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            // Pre load the variants for position indexation
            $product->getParent()->getVariants()->toArray();
        }
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->executeHandlers($event, HandlerInterface::INSERT);

        $changed |= $this->generateReference($product);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        $changed = $this->executeHandlers($event, HandlerInterface::UPDATE);

        $changed |= $this->generateReference($product);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($product);
        }

        // Schedule offers update if needed
        if ($this->persistenceHelper->isChanged($product, ['netPrice'])) {
            $this->offerInvalidator->invalidateByProductId($product->getId());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $this->executeHandlers($event, HandlerInterface::DELETE);
    }

    /**
     * Stock unit change event handler.
     *
     * @param SubjectStockUnitEvent $event
     */
    public function onStockUnitChange(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::STOCK_UNIT_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Stock unit delete event handler.
     *
     * @param SubjectStockUnitEvent $event
     */
    public function onStockUnitRemoval(SubjectStockUnitEvent $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::STOCK_UNIT_REMOVAL, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Child price change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onChildPriceChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::CHILD_PRICE_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Child availability change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onChildAvailabilityChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::CHILD_AVAILABILITY_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Child stock change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onChildStockChange(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if ($this->executeHandlers($event, HandlerInterface::CHILD_STOCK_CHANGE, true)) {
            $this->persistenceHelper->persistAndRecompute($product, true);
        }
    }

    /**
     * Queue close event handler.
     */
    public function onQueueClose()
    {
        $this->offerInvalidator->flush($this->persistenceHelper->getManager());
    }

    /**
     * Execute the event handlers method regarding to the product type,
     * and returns whether or the product has been changed.
     *
     * @param ResourceEventInterface $event
     * @param string                 $method
     * @param bool                   $skipDeleted
     *
     * @return bool
     */
    protected function executeHandlers(ResourceEventInterface $event, $method, $skipDeleted = false)
    {
        $product = $this->getProductFromEvent($event);

        if ($skipDeleted && $this->persistenceHelper->isScheduledForRemove($product)) {
            return false;
        }

        $changed = false;

        $handlers = $this->handlerRegistry->getHandlers($product);
        foreach ($handlers as $handler) {
            $changed |= call_user_func([$handler, $method], $event);
        }

        return $changed;
    }

    /**
     * Generates the product reference if it is empty.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function generateReference(ProductInterface $product)
    {
        if (0 == strlen($product->getReference())) {
            $this->referenceGenerator->generate($product);

            return true;
        }

        return false;
    }

    /**
     * Returns the product from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ProductInterface
     */
    protected function getProductFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected instance of ' . ProductInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::PRE_CREATE                => ['preCreate', 0],
            ProductEvents::PRE_UPDATE                => ['preUpdate', 0],
            ProductEvents::PRE_DELETE                => ['preDelete', 0],
            ProductEvents::INSERT                    => ['onInsert', 0],
            ProductEvents::UPDATE                    => ['onUpdate', 0],
            ProductEvents::DELETE                    => ['onDelete', 0],
            ProductEvents::STOCK_UNIT_CHANGE         => ['onStockUnitChange', 0],
            ProductEvents::CHILD_PRICE_CHANGE        => ['onChildPriceChange', 0],
            ProductEvents::CHILD_STOCK_CHANGE        => ['onChildStockChange', 0],
            ProductEvents::CHILD_AVAILABILITY_CHANGE => ['onChildAvailabilityChange', 0],
            QueueEvents::QUEUE_CLOSE                 => ['onQueueClose', 0],
        ];
    }
}
