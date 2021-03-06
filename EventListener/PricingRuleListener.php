<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\PricingRuleEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\PricingRuleInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PricingRuleListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRuleListener implements EventSubscriberInterface
{
    protected const FIELDS = ['percent', 'minQuantity'];

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var OfferInvalidator
     */
    protected $offerInvalidator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param OfferInvalidator           $offerInvalidator
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper, OfferInvalidator $offerInvalidator)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator = $offerInvalidator;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingRuleInterface
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        $this->offerInvalidator->invalidatePricing($pricingRule->getPricing());

        return $pricingRule;
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingRuleInterface
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        if ($this->persistenceHelper->isChanged($pricingRule, static::FIELDS)) {
            $this->offerInvalidator->invalidatePricing($pricingRule->getPricing());
        }

        return $pricingRule;
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingRuleInterface
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        if (null === $pricing = $pricingRule->getPricing()) {
            if (empty($cs = $this->persistenceHelper->getChangeSet($pricingRule, ['pricing']))) {
                return $pricingRule;
            }

            $pricing = $cs[0];
        }

        $this->offerInvalidator->invalidatePricing($pricing);

        return $pricingRule;
    }

    /**
     * Returns the pricing rule from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return PricingRuleInterface
     */
    protected function getPricingRuleFromEvent(ResourceEventInterface $event)
    {
        $pricingRule = $event->getResource();

        if (!$pricingRule instanceof PricingRuleInterface) {
            throw new InvalidArgumentException("Expected instance of " . PricingRuleInterface::class);
        }

        return $pricingRule;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            PricingRuleEvents::INSERT => ['onInsert', 0],
            PricingRuleEvents::UPDATE => ['onUpdate', 0],
            PricingRuleEvents::DELETE => ['onDelete', 0],
        ];
    }
}