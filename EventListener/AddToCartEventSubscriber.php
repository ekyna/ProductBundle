<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class AddToCartEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddToCartEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var string
     */
    protected $template;


    /**
     * Constructor.
     *
     * @param EngineInterface        $templating
     * @param string                 $template
     */
    public function __construct(EngineInterface $templating, $template)
    {
        $this->templating = $templating;
        $this->template = $template;
    }

    /**
     * Add to cart success handler.
     *
     * @param AddToCartEvent $event
     */
    public function onSuccess(AddToCartEvent $event)
    {
        $subject = $event->getSubject();

        if (!$subject instanceof ProductInterface) {
            return;
        }

        if (null === $modal = $event->getModal()) {
            return;
        }

        $content = $this->templating->render($this->template, [
            'product' => $subject,
        ]);

        $modal->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AddToCartEvent::SUCCESS => ['onSuccess', -1024],
        ];
    }
}
