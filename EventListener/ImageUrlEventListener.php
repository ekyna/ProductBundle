<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ImageUrlEventListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ImageUrlEventListener implements EventSubscriberInterface
{
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

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::IMAGE_URL => ['onProductImageUrl', 0],
        ];
    }
}
