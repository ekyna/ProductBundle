<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Stock\Event\SubjectStockUnitEvent;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Interface HandlerInterface
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface HandlerInterface
{
    public const INSERT                    = 'handleInsert';
    public const UPDATE                    = 'handleUpdate';
    public const DELETE                    = 'handleDelete';
    public const STOCK_UNIT_CHANGE         = 'handleStockUnitChange';
    public const STOCK_UNIT_REMOVAL        = 'handleStockUnitRemoval';
    public const CHILD_PRICE_CHANGE        = 'handleChildPriceChange';
    public const CHILD_AVAILABILITY_CHANGE = 'handleChildAvailabilityChange';
    public const CHILD_STOCK_CHANGE        = 'handleChildStockChange';


    /**
     * Handles the product insert event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleInsert(ResourceEventInterface $event);

    /**
     * Handles the product update event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleUpdate(ResourceEventInterface $event);

    /**
     * Handles the product delete event.
     *
     * @param ResourceEventInterface $event
     */
    public function handleDelete(ResourceEventInterface $event);

    /**
     * Handles the stock unit change event.
     *
     * @param SubjectStockUnitEvent $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleStockUnitChange(SubjectStockUnitEvent $event);

    /**
     * Handles the stock unit remove event.
     *
     * @param SubjectStockUnitEvent $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleStockUnitRemoval(SubjectStockUnitEvent $event);

    /**
     * Handles the child price change event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleChildPriceChange(ResourceEventInterface $event);

    /**
     * Handles the child availability (visible, quote only, end of life) change event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleChildAvailabilityChange(ResourceEventInterface $event);

    /**
     * Handles the child stock change event.
     *
     * @param ResourceEventInterface $event
     *
     * @return bool Whether or not the product has been changed.
     */
    public function handleChildStockChange(ResourceEventInterface $event);

    /**
     * Returns whether the handler supports the given product.
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function supports(ProductInterface $product);
}
