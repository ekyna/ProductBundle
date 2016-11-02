<?php

namespace Ekyna\Bundle\ProductBundle\EventListener\Handler;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class HandlerRegistry
 * @package Ekyna\Bundle\ProductBundle\EventListener\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HandlerRegistry
{
    /**
     * @var array|HandlerInterface[]
     */
    private $handlers;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->handlers = [];
    }

    /**
     * Registers the handler.
     *
     * @param HandlerInterface $handler
     */
    public function addHandler(HandlerInterface $handler)
    {
        if (in_array($handler, $this->handlers, true)) {
            throw new InvalidArgumentException("This handler is already registered.");
        }

        $this->handlers[] = $handler;
    }

    /**
     * Returns the handlers supporting the given product.
     *
     * @param ProductInterface $product
     *
     * @return array|HandlerInterface[]
     */
    public function getHandlers(ProductInterface $product)
    {
        $handlers = [];

        foreach ($this->handlers as $handler) {
            if ($handler->supports($product)) {
                $handlers[] = $handler;
            }
        }

        return $handlers;
    }
}
