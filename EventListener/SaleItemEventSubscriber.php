<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
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
     * @var ItemBuilder
     */
    private $itemBuilder;

    /**
     * @var FormBuilder
     */
    private $formBuilder;

    /**
     * @var PriceResolver
     */
    private $priceResolver;


    /**
     * Constructor.
     *
     * @param ItemBuilder   $itemBuilder
     * @param FormBuilder   $formBuilder
     * @param PriceResolver $priceResolver
     */
    public function __construct(ItemBuilder $itemBuilder, FormBuilder $formBuilder, PriceResolver $priceResolver)
    {
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formBuilder;
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

        $item = $event->getItem();

        if ($sale = $item->getSale()) {
            $this->itemBuilder->getFilter()->setCustomerGroup($sale->getCustomerGroup());
        }

        $this->itemBuilder->initialize($event->getItem());
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

        $this->itemBuilder->build($event->getItem());
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
            ->resolve($product, $item->getTotalQuantity(), $sale->getCustomerGroup(), $country);

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

        $this->formBuilder->buildItemForm($event->getForm(), $event->getItem());
    }

    /**
     * Sale item build form view event handler.
     *
     * @param SaleItemFormEvent $event
     */
    public function onSaleItemBuildFormView(SaleItemFormEvent $event)
    {
        if (!$product = $this->getProductFromEvent($event)) {
            return;
        }

        $this->formBuilder->buildItemFormView($event->getView(), $event->getItem());
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

        $provider = $this->itemBuilder->getProvider();

        if ($provider->supportsRelative($item)) {
            try {
                return $provider->resolve($item);
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
            SaleItemFormEvent::BUILD_VIEW => ['onSaleItemBuildFormView'],
        ];
    }
}
