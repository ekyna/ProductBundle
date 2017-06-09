<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceResolver;
use Ekyna\Component\Commerce\Common\Event\SaleItemAdjustmentEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Exception\SubjectException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SaleItemEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ProductProvider
     */
    private $provider;

    /**
     * @var PriceResolver
     */
    private $priceResolver;


    /**
     * Constructor.
     *
     * @param ProductProvider $provider
     * @param PriceResolver   $priceResolver
     */
    public function __construct(ProductProvider $provider, PriceResolver $priceResolver)
    {
        $this->provider = $provider;
        $this->priceResolver = $priceResolver;
    }

    /**
     * Sale item initialize event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemInitialize(SaleItemEvent $event)
    {
        if (!$product = $this->getProductFromEvent($event)) {
            return;
        }

        $this
            ->provider
            ->getItemBuilder()
            ->initialize($event->getItem());
    }

    /**
     * Sale item build event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemBuild(SaleItemEvent $event)
    {
        if (!$product = $this->getProductFromEvent($event)) {
            return;
        }

        $this
            ->provider
            ->getItemBuilder()
            ->build($event->getItem());
    }

    /**
     * Sale item build event handler.
     *
     * @param SaleItemAdjustmentEvent $event
     */
    public function onSaleItemAdjustments(SaleItemAdjustmentEvent $event)
    {
        if (!$product = $this->getProductFromEvent($event)) {
            return;
        }

        $item = $event->getItem();
        $sale = $item->getSale();
        $country = $sale->getInvoiceAddress() ? $sale->getInvoiceAddress()->getCountry() : null;

        $adjustmentsData = $this
            ->priceResolver
            ->resolve($product, $item->getQuantity(), $sale->getCustomerGroup(), $country);

        if ($adjustmentsData) {
            $event->addAdjustmentData($adjustmentsData);
        }
    }

    /**
     * Sale item build form event handler.
     *
     * @param SaleItemFormEvent $event
     */
    public function onSaleItemBuildForm(SaleItemFormEvent $event)
    {
        if (!$product = $this->getProductFromEvent($event)) {
            return;
        }

        $this
            ->provider
            ->getFormBuilder()
            ->buildItemForm($event->getForm(), $event->getItem());
    }

    /**
     * Returns the product from the given event.
     *
     * @param SaleItemEvent $event
     *
     * @return null|ProductInterface
     */
    private function getProductFromEvent(SaleItemEvent $event)
    {
        $item = $event->getItem();

        if ($this->provider->supportsRelative($item)) {
            try {
                return $this->provider->resolve($item);
            } catch (SubjectException $e) {
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SaleItemEvents::INITIALIZE    => ['onSaleItemInitialize'],
            SaleItemEvents::BUILD         => ['onSaleItemBuild'],
            SaleItemEvents::ADJUSTMENTS   => ['onSaleItemAdjustments'],
            SaleItemFormEvent::BUILD_FORM => ['onSaleItemBuildForm'],
        ];
    }
}
