<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class StockUnitsController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitsController extends AbstractController
{
    public function __construct(
        private readonly StockRenderer       $stockRenderer,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->assertXhr($request);

        $product = $this->findProductById($request->attributes->getInt('productId'));

        $list = $this
            ->stockRenderer
            ->renderSubjectStockUnits($product, [
                'class'  => 'table-condensed',
                'script' => true,
            ]);

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->translator->trans('stock_unit.label.plural', [], 'EkynaCommerce'),
            $product->getFullDesignation(true)
        );

        $modal = new Modal($title);
        $modal
            ->setHtml($list)
            ->addButton(Modal::BTN_CLOSE);

        return $this->modalRenderer->render($modal);
    }
}
