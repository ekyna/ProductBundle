<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\CheckoutEvent;
use Ekyna\Bundle\ProductBundle\Service\Highlight\Highlight;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CheckoutEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Highlight
     */
    protected $highlight;


    /**
     * Constructor.
     *
     * @param Highlight $highlight
     */
    public function __construct(Highlight $highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * Checkout event handler.
     *
     * @param CheckoutEvent $event
     */
    public function onCheckoutContent(CheckoutEvent $event)
    {
        $cart = $event->getSale();

        if (is_null($cart) || !$cart->hasItems()) {
            $event->setContent($this->highlight->renderBestSellers());
        } else {
            $event->setContent($this->highlight->renderCrossSelling(['limit' => 4]));
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutEvent::EVENT_CONTENT => ['onCheckoutContent', 0],
        ];
    }
}
