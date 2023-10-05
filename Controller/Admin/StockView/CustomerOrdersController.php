<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\AdminBundle\Table\ResourceTableHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class CustomerOrdersController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerOrdersController extends AbstractController
{
    public function __construct(
        private readonly ResourceTableHelper $tableHelper,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->assertXhr($request);

        $product = $this->findProductById($request->attributes->getInt('productId'));

        // TODO Having item not fully shipped

        $table = $this->tableHelper->createResourceTableView('ekyna_commerce.order', [
            'subject'       => $product,
            'state'         => [OrderStates::STATE_ACCEPTED],
            'shipmentState' => [
                ShipmentStates::STATE_NEW,
                ShipmentStates::STATE_PENDING,
                ShipmentStates::STATE_PARTIAL,
                ShipmentStates::STATE_PREPARATION,
            ],
            // TODO summary
        ]);

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->translator->trans('stock_view.modal.orders', [], 'EkynaProduct'),
            $product->getFullDesignation(true)
        );

        $modal = new Modal($title);
        $modal
            ->setTable($table)
            ->addButton(Modal::BTN_CLOSE);

        return $this->modalRenderer->render($modal);
    }
}
