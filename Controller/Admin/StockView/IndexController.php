<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\ProductBundle\Service\Stock\StockView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class IndexController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IndexController
{
    public function __construct(
        private readonly StockView             $stockView,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment           $twig,
        private readonly MenuBuilder           $menuBuilder
    ) {
    }

    public function __invoke(): Response
    {
        $this
            ->menuBuilder
            ->breadcrumbAppend([
                'name'         => 'ekyna_product_inventory',
                'label'        => 'stock_view.title',
                'trans_domain' => 'EkynaProduct',
                'route'        => 'admin_ekyna_product_stock_view_index',
            ]);

        $form = $this->stockView->getForm([
            'action' => $this->urlGenerator->generate('admin_ekyna_product_stock_view_export'),
            'method' => 'POST'
        ]);

        $data = $this->stockView->getContext();

        $content = $this->twig->render('@EkynaProduct/Admin/StockView/index.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);

        return (new Response($content))->setPrivate();
    }
}
