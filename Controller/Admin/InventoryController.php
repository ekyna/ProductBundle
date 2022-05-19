<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\ProductBundle\Service\Stock\Inventory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Class InventoryController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryController
{
    private Inventory             $inventory;
    private UrlGeneratorInterface $urlGenerator;
    private Environment           $twig;
    private MenuBuilder           $menuBuilder;

    public function __construct(
        Inventory             $inventory,
        UrlGeneratorInterface $urlGenerator,
        Environment           $twig,
        MenuBuilder           $menuBuilder
    ) {
        $this->inventory = $inventory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->menuBuilder = $menuBuilder;
    }

    public function index(): Response
    {
        $this
            ->menuBuilder
            ->breadcrumbAppend([
                'name'         => 'ekyna_product_inventory',
                'label'        => 'inventory.title',
                'trans_domain' => 'EkynaProduct',
                'route'        => 'admin_ekyna_product_inventory_index',
            ]);

        $form = $this->inventory->getForm([
            'action' => $this->urlGenerator->generate('admin_ekyna_product_inventory_export'),
            'method' => 'POST'
        ]);

        $data = $this->inventory->getContext();

        $content = $this->twig->render('@EkynaProduct/Admin/Inventory/index.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);

        return (new Response($content))->setPrivate();
    }

    public function products(Request $request): Response
    {
        $products = $this->inventory->listProducts($request, false, ['method' => 'GET']);

        $data = [
            'products' => $products,
        ];

        return new JsonResponse($data);
    }
}
