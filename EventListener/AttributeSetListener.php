<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\AttributeSetEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AttributeSetListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSetListener implements EventSubscriberInterface
{
    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return AttributeSetInterface
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        $attributeSet = $this->getAttributeSetFromEvent($event);

        // TODO Prevent slot removal if used by any product attribute.

        return $attributeSet;
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return AttributeSetInterface
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $attributeSet = $this->getAttributeSetFromEvent($event);

        // TODO Prevent removal if any slot is used by any product attribute.

        return $attributeSet;
    }

    /**
     * Returns the attribute set from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return AttributeSetInterface
     */
    protected function getAttributeSetFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof AttributeSetInterface) {
            throw new InvalidArgumentException('Expected instance of ' . AttributeSetInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeSetEvents::PRE_UPDATE => ['onPreUpdate', 0],
            AttributeSetEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}