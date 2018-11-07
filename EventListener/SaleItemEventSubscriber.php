<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\OfferRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\FormBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
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
     * @var OfferRepositoryInterface
     */
    protected $offerRepository;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface $contextProvider
     * @param ItemBuilder              $itemBuilder
     * @param FormBuilder              $formBuilder
     * @param OfferRepositoryInterface $offerRepository
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        ItemBuilder $itemBuilder,
        FormBuilder $formBuilder,
        OfferRepositoryInterface $offerRepository
    ) {
        $this->contextProvider = $contextProvider;
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formBuilder;
        $this->offerRepository = $offerRepository;
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

        $context = $this->contextProvider->getContext($item->getSale());

        $this->formBuilder->setContext($context);

        $this->itemBuilder->getFilter()->setContext($context);
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

        $offer = null;
        $item = $event->getItem();

        $context = $this->contextProvider->getContext($item->getSale());

        // Loop through parents and keep the best offer
        do {
            if (null !== $product = $this->getProductFromItem($item)) {
                $o = $this
                    ->offerRepository
                    ->findOneByProductAndContextAndQuantity($product, $context, $item->getTotalQuantity());

                if (is_null($o)) {
                    continue;
                }
                if (is_null($offer) || 0 <= bccomp($o['percent'], $offer['percent'], 2)) {
                    $offer = $o;
                }
            }
        } while ($item = $item->getParent());

        if (is_null($offer)) {
            return;
        }

        $type = 0 < $offer['special_offer_id'] ? 'Promotion' : 'Reduction'; // TODO translation

        $event->addAdjustmentData(new AdjustmentData(
            AdjustmentModes::MODE_PERCENT,
            sprintf('%s %s%%', $type, $offer['percent']),
            $offer['percent'] // TODO number_format
        ));
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
     * @return ProductInterface|null
     */
    protected function getProductFromEvent(SaleItemEvent $event)
    {
        $item = $event->getItem();

        return $this->getProductFromItem($item);
    }

    /**
     * Returns the product from the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return ProductInterface|null
     */
    protected function getProductFromItem(SaleItemInterface $item)
    {
        $provider = $this->itemBuilder->getProvider();

        if ($provider->supportsRelative($item)) {
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
