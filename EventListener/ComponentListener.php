<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\ComponentEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
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
    protected PersistenceHelperInterface $persistenceHelper;

    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    public function onInsert(ResourceEventInterface $event): ComponentInterface
    {
        $component = $this->getComponentFromEvent($event);

        $this->scheduleChildPriceChangeEvent($component->getParent());

        return $component;
    }

    public function onUpdate(ResourceEventInterface $event): ComponentInterface
    {
        $component = $this->getComponentFromEvent($event);

        $properties = ['parent', 'child', 'quantity', 'netPrice'];
        if (!$this->persistenceHelper->isChanged($component, $properties)) {
            return $component;
        }

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

        return $component;
    }

    public function onDelete(ResourceEventInterface $event): ComponentInterface
    {
        $component = $this->getComponentFromEvent($event);

        // Get bundle from change set if null
        if (null === $parent = $component->getParent()) {
            $parent = $this->persistenceHelper->getChangeSet($component, 'parent')[0];
        }

        $this->scheduleChildPriceChangeEvent($parent);

        return $component;
    }

    private function scheduleChildPriceChangeEvent(ProductInterface $product): void
    {
        $this->persistenceHelper->scheduleEvent($product, ProductEvents::CHILD_PRICE_CHANGE);
    }

    protected function getComponentFromEvent(ResourceEventInterface $event): ComponentInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof ComponentInterface) {
            throw new UnexpectedTypeException($resource, ComponentInterface::class);
        }

        return $resource;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ComponentEvents::INSERT => ['onInsert', 0],
            ComponentEvents::UPDATE => ['onUpdate', 0],
            ComponentEvents::DELETE => ['onDelete', 0],
        ];
    }
}
