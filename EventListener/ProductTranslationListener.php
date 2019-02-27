<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ProductTranslationEvents;
use Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductTranslationListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTranslationListener implements EventSubscriberInterface
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
     * Product translation change event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return ProductTranslationInterface
     */
    public function onChange(ResourceEventInterface $event)
    {
        $translation = $this->getTranslationFromEvent($event);

        if (null === $product = $translation->getTranslatable()) {
            $product = $this->persistenceHelper->getChangeSet($translation, 'product')[0];
        }

        if ($product) {
            $product->setUpdatedAt(new \DateTime());

            $this->persistenceHelper->persistAndRecompute($product, false);
        }

        return $translation;
    }

    /**
     * Returns the translation from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ProductTranslationInterface
     */
    protected function getTranslationFromEvent(ResourceEventInterface $event)
    {
        $translation = $event->getResource();

        if (!$translation instanceof ProductTranslationInterface) {
            throw new \UnexpectedValueException("Expected instance of " . ProductTranslationInterface::class);
        }

        return $translation;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductTranslationEvents::INSERT => ['onChange', 0],
            ProductTranslationEvents::UPDATE => ['onChange', 0],
            ProductTranslationEvents::DELETE => ['onChange', 0],
        ];
    }
}
