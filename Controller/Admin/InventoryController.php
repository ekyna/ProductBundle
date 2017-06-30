<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InventoryController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InventoryController extends Controller
{
    /**
     * Inventory index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this
            ->container
            ->get('ekyna_admin.menu.builder')
            ->breadcrumbAppend(
                'ekyna_product_inventory',
                'ekyna_product.inventory.title',
                'ekyna_product_inventory_admin_index'
            );

        $inventory = $this->get('ekyna_product.inventory');

        $form = $inventory->getSearchForm();
        $data = $inventory->getSearchData();

        return $this->render('EkynaProductBundle:Admin/Inventory:index.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Inventory products action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function productsAction(Request $request)
    {
        $products = $this->get('ekyna_product.inventory')->listProducts($request);

        $data = [
            'products' => $products,
        ];

        return new JsonResponse($data);
    }

    /**
     * Inventory stock units action.
     *
     * @param $productId
     *
     * @return string
     */
    public function stockUnitsAction($productId)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $this
            ->get('ekyna_product.product.repository')
            ->find($productId);

        $list = $this->get('ekyna_commerce.stock.stock_renderer')->renderSubjectStockUnitList($product, [
            'class' => 'table-condensed',
        ]);

        return new Response($list);
    }
}
