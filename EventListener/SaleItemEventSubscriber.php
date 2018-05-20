<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceResolver;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
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
     * @var ContextProviderInterface
     */
    protected $contextProvider;

    /**
     * @var ItemBuilder
     */
    protected $itemBuilder;

    /**
     * @var FormBuilder
     */
    protected $formBuilder;

    /**
     * @var PriceResolver
     */
    protected $priceResolver;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface $contextProvider
     * @param ItemBuilder              $itemBuilder
     * @param FormBuilder              $formBuilder
     * @param PriceResolver            $priceResolver
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        ItemBuilder $itemBuilder,
        FormBuilder $formBuilder,
        PriceResolver $priceResolver
    ) {
        $this->contextProvider = $contextProvider;
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

        $context = $this->contextProvider->getContext($item->getSale()); // TODO fallback / admin_mode

        $this->formBuilder->setContext($context);

        $this->itemBuilder->getFilter()->setCustomerGroup($context->getCustomerGroup());
        $this->itemBuilder->initialize($item);

        $item->setPrivate(false); // Root items can't be private.
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

        $item = $event->getItem();

        $this->itemBuilder->build($item);

        $item->setPrivate(false); // Root items can't be private.
    }

    /**
     * Sale item build event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemAdjustments(SaleItemEvent $event)
    {
        if (!$product = $this->getProductFromEvent($event)) {
            return;
        }

        $item = $event->getItem();

        $context = $this->contextProvider->getContext($item->getSale());

        $adjustmentsData = $this
            ->priceResolver
            ->resolve($product, $context, $item->getTotalQuantity());

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
    protected function getProductFromEvent(SaleItemEvent $event)
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
            SaleItemEvents::DISCOUNT      => ['onSaleItemAdjustments'],
            SaleItemFormEvent::BUILD_FORM => ['onSaleItemBuildForm'],
            SaleItemFormEvent::BUILD_VIEW => ['onSaleItemBuildFormView'],
        ];
    }
}
