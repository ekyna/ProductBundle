<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\ProductBundle\Event\PricingRuleEvents;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
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

    protected PersistenceHelperInterface $persistenceHelper;
    protected OfferInvalidator           $offerInvalidator;

    public function __construct(PersistenceHelperInterface $persistenceHelper, OfferInvalidator $offerInvalidator)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->offerInvalidator = $offerInvalidator;
    }

    public function onInsert(ResourceEventInterface $event): PricingRuleInterface
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        $this->offerInvalidator->invalidatePricing($pricingRule->getPricing());

        return $pricingRule;
    }

    public function onUpdate(ResourceEventInterface $event): PricingRuleInterface
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        if ($this->persistenceHelper->isChanged($pricingRule, static::FIELDS)) {
            $this->offerInvalidator->invalidatePricing($pricingRule->getPricing());
        }

        return $pricingRule;
    }

    public function onDelete(ResourceEventInterface $event): PricingRuleInterface
    {
        $pricingRule = $this->getPricingRuleFromEvent($event);

        if (null === $pricing = $pricingRule->getPricing()) {
            if (empty($cs = $this->persistenceHelper->getChangeSet($pricingRule, 'pricing'))) {
                return $pricingRule;
            }

            $pricing = $cs[0];
        }

        $this->offerInvalidator->invalidatePricing($pricing);

        return $pricingRule;
    }

    protected function getPricingRuleFromEvent(ResourceEventInterface $event): PricingRuleInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof PricingRuleInterface) {
            throw new UnexpectedTypeException($resource, PricingRuleInterface::class);
        }

        return $resource;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PricingRuleEvents::INSERT => ['onInsert', 0],
            PricingRuleEvents::UPDATE => ['onUpdate', 0],
            PricingRuleEvents::DELETE => ['onDelete', 0],
        ];
    }
}
