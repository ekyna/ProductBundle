<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UrlEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UrlEventSubscriber implements EventSubscriberInterface
{
    /**
     * Product image url event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onProductImageUrl(ResourceEventInterface $event): void
    {
        $resource = $event->getResource();

        if (!$resource instanceof ProductInterface) {
            return;
        }

        $event->stopPropagation();

        if (null === $image = $resource->getImage()) {
            return;
        }

        $event
            ->addData('route', 'ekyna_media_download')
            ->addData('parameters', [
                'key' => $image->getPath(),
            ]);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::IMAGE_URL => ['onProductImageUrl', 0],
        ];
    }
}
