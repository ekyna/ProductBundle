<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductMediaEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductMediaInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductMediaEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductMediaEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;


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
     */
    public function onChange(ResourceEventInterface $event)
    {
        $media = $event->getResource();

        if (!$media instanceof ProductMediaInterface) {
            throw new \UnexpectedValueException("Expected instance of " . ProductMediaInterface::class);
        }

        if (null === $product = $media->getProduct()) {
            $product = $this->persistenceHelper->getChangeSet($media, 'product')[0];
        }

        if ($product) {
            $product->setUpdatedAt(new \DateTime());

            $this->persistenceHelper->persistAndRecompute($product, false);
        }
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
