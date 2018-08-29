<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\PricingRuleEvents;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingRuleInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Component\Resource\Event\ResourceEvent;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PricingRuleEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRuleEventSubscriber implements EventSubscriberInterface
{
    private const FIELDS = ['percent', 'minQuantity'];

    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var OfferInvalidator
     */
    private $offerInvalidator;


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
     * @param ResourceEvent $event
     */
    public function onInsert(ResourceEvent $event)
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        $this->invalidateOffers($pricingRule->getPricing());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEvent $event
     */
    public function onUpdate(ResourceEvent $event)
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        if ($this->persistenceHelper->isChanged($pricingRule, static::FIELDS)) {
            $this->invalidateOffers($pricingRule->getPricing());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEvent $event
     */
    public function onDelete(ResourceEvent $event)
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        if (null === $pricing = $pricingRule->getPricing()) {
            if (empty($cs = $this->persistenceHelper->getChangeSet($pricingRule, ['pricing']))) {
                return;
            }

            $pricing = $cs[0];
        }

        $this->invalidateOffers($pricing);
    }

    /**
     * Invalidates offers for the given pricing.
     *
     * @param PricingInterface $pricing
     */
    private function invalidateOffers(PricingInterface $pricing)
    {
        foreach ($pricing->getBrands() as $brand) {
            $this->offerInvalidator->invalidateByBrandId($brand->getId());
        }
    }

    /**
     * Returns the pricing rule from the event.
     *
     * @param ResourceEvent $event
     *
     * @return PricingRuleInterface
     */
    private function getPricingRuleFromEvent(ResourceEvent $event)
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