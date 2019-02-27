<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductMediaEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductMediaListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductMediaListener implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function __construct(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Product media change event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return ProductMediaInterface
     */
    public function onChange(ResourceEventInterface $event)
    {
        $media = $this->getMediaFromEvent($event);

        if (null === $product = $media->getProduct()) {
            $product = $this->persistenceHelper->getChangeSet($media, 'product')[0];
        }

        if ($product) {
            $product->setUpdatedAt(new \DateTime());

            $this->persistenceHelper->persistAndRecompute($product, false);
        }

        return $media;
    }

    /**
     * Returns the media from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ProductMediaInterface
     */
    protected function getMediaFromEvent(ResourceEventInterface $event)
    {
        $media = $event->getResource();

        if (!$media instanceof ProductMediaInterface) {
            throw new \UnexpectedValueException("Expected instance of " . ProductMediaInterface::class);
        }

        return $media;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductMediaEvents::INSERT => ['onChange', 0],
            ProductMediaEvents::UPDATE => ['onChange', 0],
            ProductMediaEvents::DELETE => ['onChange', 0],
        ];
    }
}
