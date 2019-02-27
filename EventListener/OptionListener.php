<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\OptionEvents;
use Ekyna\Bundle\ProductBundle\Event\ProductEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OptionListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionListener implements EventSubscriberInterface
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
     * @return OptionInterface
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $option = $this->getOptionFromEvent($event);

        $this->handleDataFields($option);

        $this->scheduleChildPriceChangeEvent($option->getGroup()->getProduct());

        return $option;
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return OptionInterface
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $option = $this->getOptionFromEvent($event);

        $this->handleDataFields($option);

        if ($this->persistenceHelper->isChanged($option, ['netPrice', 'product'])) {
            $this->scheduleChildPriceChangeEvent($option->getGroup()->getProduct());
        }

        return $option;
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return OptionInterface
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $option = $this->getOptionFromEvent($event);

        if (null === $group = $option->getGroup()) {
            $group = $this->persistenceHelper->getChangeSet($option, 'group')[0];
        }
        if (null === $product = $group->getProduct()) {
            $product = $this->persistenceHelper->getChangeSet($group, 'product')[0];
        }
        if (null !== $product) {
            $this->scheduleChildPriceChangeEvent($product);
        }

        return $option;
    }

    /**
     * Clears the data fields of a product is bound to the option.
     *
     * @param OptionInterface $option
     */
    protected function handleDataFields(OptionInterface $option)
    {
        $changed = false;

        if (null !== $product = $option->getProduct()) {
            if (null !== $option->getDesignation()) {
                $option->setDesignation(null);
                $changed = true;
            }
            if (null !== $option->getReference()) {
                $option->setReference(null);
                $changed = true;
            }
            if (null !== $option->getWeight()) {
                $option->setWeight(null);
                $changed = true;
            }
            if (null !== $option->getTaxGroup()) {
                $option->setTaxGroup(null);
                $changed = true;
            }

            $translations = $option->getTranslations();
            if (0 < $translations->count()) {
                foreach ($translations as $translation) {
                    $option->removeTranslation($translation);
                    $this->persistenceHelper->remove($translation);
                }
                $changed = true;
            }
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($option);
        }
    }

    /**
     * Dispatches the child price change events.
     *
     * @param ProductInterface $product
     */
    protected function scheduleChildPriceChangeEvent(ProductInterface $product)
    {
        $this->persistenceHelper->scheduleEvent(ProductEvents::CHILD_PRICE_CHANGE, $product);
    }

    /**
     * Returns the option from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return OptionInterface
     * @throws InvalidArgumentException
     */
    protected function getOptionFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OptionInterface) {
            throw new InvalidArgumentException('Expected instance of ' . OptionInterface::class);
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OptionEvents::INSERT => ['onInsert', 0],
            OptionEvents::UPDATE => ['onUpdate', 0],
            OptionEvents::DELETE => ['onDelete', 0],
        ];
    }
}
