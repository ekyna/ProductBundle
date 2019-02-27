<?php

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleButtonsEvent;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CoreBundle\Model\UiButton;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SaleButtonsEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleButtonsEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;


    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Sale buttons event handler.
     *
     * @param SaleButtonsEvent $event
     */
    public function onSaleButtons(SaleButtonsEvent $event)
    {
        $sale = $event->getSale();

        if (0 === $sale->getItems()->count()) {
            return;
        }

        if ($sale instanceof OrderInterface) {
            $type = 'order';
        } elseif ($sale instanceof QuoteInterface) {
            $type = 'quote';
        } else {
            return;
        }

        $path = $this->urlGenerator->generate('ekyna_product_catalog_admin_render_from_sale', [
            'type' => $type,
            'id'   => $sale->getId(),
        ]);

        $event->addButton(new UiButton('ekyna_product.catalog.button.render_from_sale', [
            'path'  => $path,
            'theme' => 'default',
            'type'  => 'link',
            'icon'  => 'file',
        ]));
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SaleButtonsEvent::SALE_BUTTONS => ['onSaleButtons', 0],
        ];
    }
}
