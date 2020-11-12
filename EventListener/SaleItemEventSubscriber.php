<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemFormEvent;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
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
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface $contextProvider
     * @param ItemBuilder              $itemBuilder
     * @param FormBuilder              $formBuilder
     * @param OfferRepositoryInterface $offerRepository
     * @param TranslatorInterface      $translator
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        ItemBuilder $itemBuilder,
        FormBuilder $formBuilder,
        OfferRepositoryInterface $offerRepository,
        TranslatorInterface $translator
    ) {
        $this->contextProvider = $contextProvider;
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formBuilder;
        $this->offerRepository = $offerRepository;
        $this->translator = $translator;
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
     * Sale item discount event handler.
     *
     * @param SaleItemEvent $event
     */
    public function onSaleItemDiscount(SaleItemEvent $event)
    {
        if (!$product = $this->getProductFromEvent($event)) {
            return;
        }

        $offer = null;
        $item = $event->getItem();

        $context = $this->contextProvider->getContext($item->getSale());

        // Loop through parents and keep the best offer
        do {
            if (null === $product = $this->getProductFromItem($item)) {
                continue;
            }

            $o = $this
                ->offerRepository
                ->findOneByProductAndContextAndQuantity($product, $context, $item->getTotalQuantity());

            if (!is_null($o) && (is_null($offer) || 0 <= bccomp($o['percent'], $offer['percent'], 2))) {
                $offer = $o;
            }

            // Options should not inherit
            if ($item->hasData(ItemBuilder::OPTION_GROUP_ID) || $item->hasData(ItemBuilder::OPTION_ID)) {
                break;
            }
        } while ($item = $item->getParent());

        if (is_null($offer)) {
            return;
        }

        if (0 < $offer['special_offer_id']) {
            $type = $this->translator->trans('ekyna_product.special_offer.label.singular');
            $source = 'special_offer:' . $offer['special_offer_id'];
        } elseif (0 < $offer['pricing_id']) {
            $type = $this->translator->trans('ekyna_product.pricing.label.singular');
            $source = 'pricing_id:' . $offer['pricing_id'];
        } else {
            throw new RuntimeException("Unexpected offer type.");
        }

        $event->addAdjustmentData(new AdjustmentData(
            AdjustmentModes::MODE_PERCENT,
            sprintf('%s %s%%', $type, $offer['percent']),
            $offer['percent'], // TODO number_format
            $source
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

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
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
