<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\SaleButtonsEvent;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\ProductBundle\Action\Admin\Catalog\RenderFromSaleAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\UiBundle\Model\UiButton;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SaleButtonsEventSubscriber
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleButtonsEventSubscriber implements EventSubscriberInterface
{
    private ResourceHelper $resourceHelper;

    public function __construct(ResourceHelper $resourceHelper)
    {
        $this->resourceHelper = $resourceHelper;
    }

    public function onSaleButtons(SaleButtonsEvent $event): void
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

        $path = $this->resourceHelper->generateResourcePath('ekyna_product.catalog', RenderFromSaleAction::class,[
            'type' => $type,
            'id'   => $sale->getId(),
        ]);

        $event->addButton(new UiButton(t('catalog.button.render_from_sale', [], 'EkynaProduct'), [
            'path'  => $path,
            'theme' => 'default',
            'type'  => 'link',
            'icon'  => 'file',
        ]));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SaleButtonsEvent::SALE_BUTTONS => ['onSaleButtons', 0],
        ];
    }
}
