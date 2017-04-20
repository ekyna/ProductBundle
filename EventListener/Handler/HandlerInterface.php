<?php

declare(strict_types=1);

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
    public const DI_TAG = 'ekyna_product.product_event_handler';

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
     * @return bool Whether the product has been changed.
     */
    public function handleInsert(ResourceEventInterface $event): bool;

    /**
     * Handles the product update event.
     *
     * @return bool Whether the product has been changed.
     */
    public function handleUpdate(ResourceEventInterface $event): bool;

    /**
     * Handles the product delete event.
     *
     * @return bool Whether the product has been changed.
     */
    public function handleDelete(ResourceEventInterface $event): bool;

    /**
     * Handles the stock unit change event.
     *
     * @return bool Whether the product has been changed.
     */
    public function handleStockUnitChange(SubjectStockUnitEvent $event): bool;

    /**
     * Handles the stock unit remove event.
     *
     * @return bool Whether the product has been changed.
     */
    public function handleStockUnitRemoval(SubjectStockUnitEvent $event): bool;

    /**
     * Handles the child price change event.
     *
     * @return bool Whether the product has been changed.
     */
    public function handleChildPriceChange(ResourceEventInterface $event): bool;

    /**
     * Handles the child availability (visible, quote only, end of life) change event.
     *
     * @return bool Whether the product has been changed.
     */
    public function handleChildAvailabilityChange(ResourceEventInterface $event): bool;

    /**
     * Handles the child stock change event.
     *
     * @return bool Whether the product has been changed.
     */
    public function handleChildStockChange(ResourceEventInterface $event): bool;

    /**
     * Returns whether the handler supports the given product.
     */
    public function supports(ProductInterface $product): bool;
}
