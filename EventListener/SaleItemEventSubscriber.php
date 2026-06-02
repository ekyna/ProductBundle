<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceGridGuesser;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SaleItemEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly ContextProviderInterface $contextProvider,
        protected readonly ItemBuilder $itemBuilder,
        protected readonly FormBuilder $formBuilder,
        protected readonly PriceGridGuesser $priceGridGuesser,
        protected readonly OfferRepositoryInterface $offerRepository,
        protected readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Sale item initialize event handler.
     */
    public function onSaleItemInitialize(SaleItemEvent $event): void
    {
        if (null === $this->getProductFromEvent($event)) {
            return;
        }

        $item = $event->getItem();

        $context = $this->contextProvider->getContext($item->getRootSale());

        $this->formBuilder->setContext($context);

        $this->itemBuilder->getFilter()->setContext($context);
        $this->itemBuilder->initialize($item);

        $item->setPrivate(false); // Root items can't be private.
    }

    /**
     * Sale item build event handler.
     */
    public function onSaleItemBuild(SaleItemEvent $event): void
    {
        if (null === $this->getProductFromEvent($event)) {
            return;
        }

        $item = $event->getItem();

        $this->itemBuilder->build($item);

        $item->setPrivate(false); // Root items can't be private.
    }

    /**
     * Sale item discount event handler.
     */
    public function onSaleItemDiscount(SaleItemEvent $event): void
    {
        if (null === $this->getProductFromEvent($event)) {
            return;
        }


        $item = $event->getItem();

        if (null === $product = $this->getProductFromItem($item)) {
            return;
        }

        $context = $this->contextProvider->getContext($item->getRootSale());

        // PRICE GRID SYSTEM

        $price = $this
            ->priceGridGuesser
            ->guess($product, $context->getCustomerGroup(), $item->getTotalQuantity());

        if (null !== $price) {
            $item->setNetPrice($price);

            return;
        }

        // PRICING SYSTEM

        // TODO Move AdjustmentData build in a dedicated service.

        $offer = null;

        // Loop through parents and keep the best offer
        while ($product) {
            $o = $this
                ->offerRepository
                ->findOneByProductAndContextAndQuantity($product, $context, $item->getTotalQuantity());

            if (!is_null($o) && (is_null($offer) || $o['percent'] > $offer['percent'])) {
                $offer = $o;
            }

            // Options should not inherit
            if ($item->hasDatum(ItemBuilder::OPTION_GROUP_ID) || $item->hasDatum(ItemBuilder::OPTION_ID)) {
                break;
            }

            if (null === $item = $item->getParent()) {
                break;
            }

            $product = $this->getProductFromItem($item);
        }

        if (is_null($offer)) {
            return;
        }

        if (0 < $offer['special_offer_id']) {
            $type = $this->translator->trans('special_offer.label.singular', [], 'EkynaProduct');
            $source = 'special_offer:' . $offer['special_offer_id'];
        } elseif (0 < $offer['pricing_id']) {
            $type = $this->translator->trans('pricing.label.singular', [], 'EkynaProduct');
            $source = 'pricing_id:' . $offer['pricing_id'];
        } else {
            throw new RuntimeException('Unexpected offer type.');
        }

        $event->addAdjustmentData(new AdjustmentData(
            AdjustmentModes::MODE_PERCENT,
            sprintf('%s %s%%', $type, $offer['percent']),
            $offer['percent'],
            $source
        ));
    }

    /**
     * Sale item build form event handler.
     */
    public function onSaleItemBuildForm(SaleItemFormEvent $event): void
    {
        if (!$this->getProductFromEvent($event)) {
            return;
        }

        $this->formBuilder->buildItemForm($event->getForm(), $event->getItem());
    }

    /**
     * Sale item build form view event handler.
     */
    public function onSaleItemBuildFormView(SaleItemFormEvent $event): void
    {
        if (!$this->getProductFromEvent($event)) {
            return;
        }

        $this->formBuilder->buildItemFormView($event->getView(), $event->getItem());
    }

    /**
     * Returns the product from the given event.
     */
    protected function getProductFromEvent(SaleItemEvent $event): ?ProductInterface
    {
        $item = $event->getItem();

        return $this->getProductFromItem($item);
    }

    /**
     * Returns the product from the given sale item.
     */
    protected function getProductFromItem(SaleItemInterface $item): ?ProductInterface
    {
        $provider = $this->itemBuilder->getProvider();

        if ($provider->supportsReference($item)) {
            try {
                $subject = $provider->resolve($item);

                if ($subject instanceof ProductInterface) {
                    return $subject;
                }
            } catch (SubjectException $e) {
            }
        }

        return null;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SaleItemEvents::INITIALIZE    => ['onSaleItemInitialize'],
            SaleItemEvents::BUILD         => ['onSaleItemBuild'],
            SaleItemEvents::DISCOUNT      => ['onSaleItemDiscount'],
            SaleItemFormEvent::BUILD_FORM => ['onSaleItemBuildForm'],
            SaleItemFormEvent::BUILD_VIEW => ['onSaleItemBuildFormView'],
        ];
    }
}
