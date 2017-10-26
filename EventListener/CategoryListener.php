<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\CategoryEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\CategoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CategoryListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CategoryListener implements EventSubscriberInterface
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
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $category = $this->getCategoryFromEvent($event);

        $this->fixVisibility($category);

        $this->fixChildrenVisibility($category);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $category = $this->getCategoryFromEvent($event);

        $this->fixVisibility($category);

        if (!$category->isVisible() && $this->persistenceHelper->isChanged($category, 'visible')) {
            $this->fixChildrenVisibility($category);
        }
    }

    /**
     * Fixes the category's visibility regarding to the parent's visibility.
     *
     * @param CategoryInterface $category
     */
    private function fixVisibility(CategoryInterface $category)
    {
        $parent = $category->getParent();
        if (null !== $parent && !$parent->isVisible() && $category->isVisible()) {
            $category->setVisible(false);
        }
    }

    /**
     * Fix the category's children visibility recursively.
     *
     * @param CategoryInterface $category
     */
    private function fixChildrenVisibility(CategoryInterface $category)
    {
        if (0 === $category->getChildren()->count()) {
            return;
        }

        if (!$category->isVisible()) {
            foreach ($category->getChildren() as $child) {
                if ($child->isVisible()) {
                    $child->setVisible(false);

                    $this->persistenceHelper->persistAndRecompute($child, true); // Schedule event for recursion
                }
            }
        }
    }

    /**
     * Returns the category from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return CategoryInterface
     * @throws InvalidArgumentException
     */
    private function getCategoryFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CategoryInterface) {
            throw new InvalidArgumentException('Expected instance of ' . CategoryInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CategoryEvents::INSERT => ['onInsert', 0],
            CategoryEvents::UPDATE => ['onUpdate', 0],
        ];
    }
}
