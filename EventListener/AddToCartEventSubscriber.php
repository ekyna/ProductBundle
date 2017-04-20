<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\AddToCartEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

/**
 * Class AddToCartEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddToCartEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $template;

    public function __construct(Environment $twig, string $template)
    {
        $this->twig = $twig;
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

        $content = $this->twig->render($this->template, [
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
