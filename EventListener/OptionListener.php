<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\OptionEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
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
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $option = $this->getOptionFromEvent($event);

        if ($this->handleDataFields($option)) {
            $this->persistenceHelper->persistAndRecompute($option);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $option = $this->getOptionFromEvent($event);

        if ($this->handleDataFields($option)) {
            $this->persistenceHelper->persistAndRecompute($option);
        }
    }

    /**
     * Clears the data fields of a product is bound to the option.
     *
     * @param OptionInterface $option
     *
     * @return bool Whether the option has been changed.
     */
    private function handleDataFields(OptionInterface $option)
    {
        $changed = false;

        if (null !== $product = $option->getProduct()) {
            if (null === $option->getNetPrice()) {
                $option->setNetPrice($product->getNetPrice());
                $changed = true;
            }

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

        return $changed;
    }

    /**
     * Returns the option from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return OptionInterface
     * @throws InvalidArgumentException
     */
    private function getOptionFromEvent(ResourceEventInterface $event)
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
        ];
    }
}
