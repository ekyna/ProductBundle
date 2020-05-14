<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\EventListener\Handler\HandlerInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceInvalidator;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Resource\Event\QueueEvents;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductListener implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var Handler\HandlerRegistry
     */
    protected $handlerRegistry;

    /**
     * @var GeneratorInterface
     */
    protected $referenceGenerator;

    /**
     * @var OfferInvalidator
     */
    protected $offerInvalidator;

    /**
     * @var PriceInvalidator
     */
    protected $priceInvalidator;

    /**
     * @var array
     */
    private $stockDefaults;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param Handler\HandlerRegistry    $registry
     * @param GeneratorInterface         $referenceGenerator
     * @param OfferInvalidator           $offerInvalidator
     * @param PriceInvalidator           $priceInvalidator
     * @param array                      $stockDefaults
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        Handler\HandlerRegistry $registry,
        GeneratorInterface $referenceGenerator,
        OfferInvalidator $offerInvalidator,
        PriceInvalidator $priceInvalidator,
        array $stockDefaults
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->handlerRegistry = $registry;
        $this->referenceGenerator = $referenceGenerator;
        $this->offerInvalidator = $offerInvalidator;
        $this->priceInvalidator = $priceInvalidator;
        $this->stockDefaults = $stockDefaults;
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        $product = $this->getProductFromEvent($event);

        if (ProductTypes::isChildType($product)) {
            $this->setStockDefaults($product);
        }
    }

    /**
     * Sets the product's stock defaults.
     *
     * @param ProductInterface $product
     */
    private function setStockDefaults(ProductInterface $product)
    {
        $map = [
            'stock_mode'             => 'setStockMode',
            'stock_floor'            => 'setStockFloor',
            'replenishment_time'     => 'setReplenishmentTime',
            'minimum_order_quantity' => 'setMinimumOrderQuantity',
            'quote_only'             => 'setQuoteOnly',
            'end_of_life'            => 'setEndOfLife',
        ];

        foreach ($map as $key => $method) {
            if (isset($this->stockDefaults[$key])) {
                $product->{$method}($this->stockDefaults[$key]);
            }
        }
    }

    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreCreate(ResourceEventInterface $event)
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
    public function onPreUpdate(ResourceEventInterface $event)
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
    public function onPreDelete(ResourceEventInterface $event)
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
        if ($this->persistenceHelper->isChanged($product, ['netPrice', 'brand'])) {
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
        $manager = $this->persistenceHelper->getManager();

        $manager->getConnection()->transactional(function () use ($manager) {
            $this->offerInvalidator->flush($manager);
            $this->priceInvalidator->flush($manager);
        });
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
        if (!empty($product->getReference())) {
            return false;
        }

        $product->setReference($this->referenceGenerator->generate($product));

        return true;
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
            ProductEvents::INITIALIZE                => ['onInitialize', 0],
            ProductEvents::PRE_CREATE                => ['onPreCreate', 0],
            ProductEvents::PRE_UPDATE                => ['onPreUpdate', 0],
            ProductEvents::PRE_DELETE                => ['onPreDelete', 0],
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
