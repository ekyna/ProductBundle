<?php

namespace Ekyna\Bundle\ProductBundle\Service\Google;

use Ekyna\Bundle\GoogleBundle\Tracking\Commerce\Product;
use Ekyna\Bundle\GoogleBundle\Tracking\Event;
use Ekyna\Bundle\GoogleBundle\Tracking\TrackingPool;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class TrackingHelper
 * @package Ekyna\Bundle\ProductBundle\Service\Google
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TrackingHelper
{
    /**
     * @var TrackingPool
     */
    private $pool;


    /**
     * Constructor.
     *
     * @param TrackingPool $pool
     */
    public function __construct(TrackingPool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * Tracks the given products view event.
     *
     * @param array  $products
     * @param string $type
     */
    public function track(array $products, string $type = Event::VIEW_ITEM)
    {
        if (empty($products)) {
            return;
        }

        $event = new Event($type);

        foreach ($products as $product) {
            if (!$product instanceof ProductInterface) {
                continue;
            }

            $item = new Product($product->getReference(), $product->getFullDesignation());
            $item
                ->setPrice($product->getMinPrice());

            $event->addItem($item);
        }

        $this->pool->addEvent($event);
    }
}
