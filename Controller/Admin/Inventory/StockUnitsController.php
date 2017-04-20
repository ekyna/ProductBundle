<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory;

use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class StockUnitsController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitsController extends AbstractController
{
    private StockRenderer       $stockRenderer;
    private TranslatorInterface $translator;

    public function __construct(StockRenderer $stockRenderer, TranslatorInterface $translator)
    {
        $this->stockRenderer = $stockRenderer;
        $this->translator = $translator;
    }

    public function __invoke(Request $request): Response
    {
        $this->assertXhr($request);

        $product = $this->findProductById($request->attributes->get('productId'));

        $list = $this
            ->stockRenderer
            ->renderSubjectStockUnits($product, [
                'class'  => 'table-condensed',
                'script' => true,
            ]);

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->translator->trans('stock_unit.label.plural', [], 'EkynaCommerce'),
            $product->getFullTitle()
        );

        $modal = new Modal($title);
        $modal
            ->setHtml($list)
            ->addButton(Modal::BTN_CLOSE);

        return $this->modalRenderer->render($modal);
    }
}
