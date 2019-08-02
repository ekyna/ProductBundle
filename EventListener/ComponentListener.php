<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ComponentEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ComponentInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ComponentListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ComponentListener implements EventSubscriberInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return ComponentInterface
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $component = $this->getComponentFromEvent($event);

        $this->scheduleChildPriceChangeEvent($component->getParent());

        return $component;
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return ComponentInterface
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $component = $this->getComponentFromEvent($event);

        $properties = ['parent', 'child', 'quantity', 'netPrice'];
        if ($this->persistenceHelper->isChanged($component, $properties)) {
            if (!empty($cs = $this->persistenceHelper->getChangeSet($component, 'parent'))) {
                if ($parent = $cs[0]) {
                    $this->scheduleChildPriceChangeEvent($parent);
                }
                if ($parent = $cs[1]) {
                    $this->scheduleChildPriceChangeEvent($parent);
                }
            } else {
                $this->scheduleChildPriceChangeEvent($component->getParent());
            }
        }

        return $component;
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return ComponentInterface
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $component = $this->getComponentFromEvent($event);

        // Get bundle from change set if null
        if (null === $parent = $component->getParent()) {
            $parent = $this->persistenceHelper->getChangeSet($component, 'parent')[0];
        }

        $this->scheduleChildPriceChangeEvent($parent);

        return $component;
    }

    /**
     * Dispatches the child price change events.
     *
     * @param ProductInterface $product
     */
    private function scheduleChildPriceChangeEvent(ProductInterface $product)
    {
        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_PRICE_CHANGE, $product);
    }

    /**
     * Returns the component from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ComponentInterface
     * @throws InvalidArgumentException
     */
    protected function getComponentFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof ComponentInterface) {
            throw new InvalidArgumentException('Expected instance of ' . ComponentInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ComponentEvents::INSERT => ['onInsert', 0],
            ComponentEvents::UPDATE => ['onUpdate', 0],
            ComponentEvents::DELETE => ['onDelete', 0],
        ];
    }
}
