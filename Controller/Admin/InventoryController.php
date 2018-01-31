<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Bundle\ProductBundle\Form\Type\Inventory\QuickEditType;
use Ekyna\Bundle\ProductBundle\Form\Type\Inventory\ResupplyType;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $form = $inventory->getForm([
            'action' => $this->generateUrl('ekyna_product_inventory_admin_products'),
        ]);

        $data = $inventory->getContext();

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
        $products = $this
            ->get('ekyna_product.inventory')
            ->listProducts($request);

        $data = [
            'products' => $products,
        ];

        return new JsonResponse($data);
    }

    /**
     * Quick edit action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function quickEditAction(Request $request)
    {
        if (!$request->isXmlHttpRequest() && !$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException('Not yet implemented. Only XHR is supported.');
        }

        $product = $this->findProductByRequest($request);

        $form = $this->createForm(QuickEditType::class, $product, [
            'action' => $this->generateUrl('ekyna_product_inventory_admin_quick_edit', [
                'productId' => $product->getId(),
            ]),
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_product.product.operator')->update($product);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } else {
                return new JsonResponse([
                    'success' => true,
                ]);
            }
        }

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->getTranslator()->trans('ekyna_product.product.button.edit'),
            $product->getFullTitle()
        );

        $modal = new Modal($title, $form->createView(), [
            [
                'id'       => 'submit',
                'label'    => 'ekyna_core.button.save',
                'icon'     => 'glyphicon glyphicon-ok',
                'cssClass' => 'btn-success',
                'autospin' => true,
            ],
            [
                'id'       => 'close',
                'label'    => 'ekyna_core.button.cancel',
                'icon'     => 'glyphicon glyphicon-remove',
                'cssClass' => 'btn-default',
            ],
        ]);
        $modal->setVars([
            'form_template' => 'EkynaProductBundle:Admin/Inventory:_quick_edit_form.html.twig',
        ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Inventory stock units action.
     *
     * @param Request $request
     *
     * @return string
     */
    public function stockUnitsAction(Request $request)
    {
        if (!$request->isXmlHttpRequest() && !$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException('Only XHR is supported.');
        }

        $product = $this->findProductByRequest($request);

        $list = $this
            ->get('ekyna_commerce.stock.stock_renderer')
            ->renderSubjectStockUnits($product, [
                'class'  => 'table-condensed',
                'script' => true,
            ]);

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->getTranslator()->trans('ekyna_commerce.stock_unit.label.plural'),
            $product->getFullTitle()
        );

        $modal = new Modal($title, $list, [
            [
                'id'       => 'close',
                'label'    => 'ekyna_core.button.close',
                'icon'     => 'glyphicon glyphicon-remove',
                'cssClass' => 'btn-default',
            ],
        ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Inventory customer orders action.
     *
     * @param Request $request
     *
     * @return string
     */
    public function customerOrdersAction(Request $request)
    {
        if (!$request->isXmlHttpRequest() && !$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException('Only XHR is supported.');
        }

        $product = $this->findProductByRequest($request);

        $orderConfig = $this->get('ekyna_commerce.order.configuration');

        $table = $this
            ->get('table.factory')
            ->createTable($orderConfig->getResourceName(), $orderConfig->getTableType(), [
                'subject'       => $product,
                'state'         => [OrderStates::STATE_ACCEPTED],
                'shipmentState' => [ShipmentStates::STATE_NEW, ShipmentStates::STATE_PENDING, ShipmentStates::STATE_PARTIAL],
                // TODO limit => 100 (no paggging)
                // TODO summary
            ]);

        if (null !== $response = $table->handleRequest($request)) {
            return $response;
        }

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->getTranslator()->trans('ekyna_product.inventory.modal.orders'),
            $product->getFullTitle()
        );

        $modal = new Modal($title, $table->createView(), [
            [
                'id'       => 'close',
                'label'    => 'ekyna_core.button.close',
                'icon'     => 'glyphicon glyphicon-remove',
                'cssClass' => 'btn-default',
            ],
        ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Resupply action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function resupplyAction(Request $request)
    {
        /*if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet implemented. Only XHR is supported.');
        }*/

        $product = $this->findProductByRequest($request);

        $form = $this->createForm(ResupplyType::class, [], [
            'action'  => $this->generateUrl('ekyna_product_inventory_admin_resupply', [
                'productId' => $product->getId(),
            ]),
            'product' => $product,
            'attr'    => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface $supplierProduct */
            $supplierProduct = $this
                ->get('ekyna_commerce.supplier_product.repository')
                ->find($request->request->get('supplierProduct'));

            if (null !== $supplierProduct) {
                /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
                $supplierOrder = null;
                if (0 < $supplierOrderId = intval($request->request->get('supplierOrder'))) {
                    $supplierOrder = $this->get('ekyna_commerce.supplier_order.repository')->find($supplierOrderId);
                }

                $quantity = $form->get('quantity')->getData();
                $netPrice = $form->get('netPrice')->getData();
                $estimatedDateOfArrival = $form->get('estimatedDateOfArrival')->getData();

                $resupply = $this->get('ekyna_product.resupply');

                $event = $resupply->resupply(
                    $supplierProduct,
                    $quantity,
                    $netPrice,
                    $supplierOrder,
                    $estimatedDateOfArrival
                );

                if ($event->hasErrors()) {
                    foreach ($event->getErrors() as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                } else {
                    return new JsonResponse([
                        'success' => true,
                    ]);
                }
            } else {
                $form->addError(new FormError('Veuillez choisir une référence fournisseur.'));
            }
        }

        $title = sprintf(
            '%s <small style="font-style:italic">%s</small>',
            $this->getTranslator()->trans('ekyna_product.inventory.modal.resupply'),
            $product->getFullTitle()
        );

        $modal = new Modal($title, $form->createView(), [
            [
                'id'       => 'submit',
                'label'    => 'ekyna_core.button.save',
                'icon'     => 'glyphicon glyphicon-ok',
                'cssClass' => 'btn-success',
                'autospin' => true,
            ],
            [
                'id'       => 'close',
                'label'    => 'ekyna_core.button.cancel',
                'icon'     => 'glyphicon glyphicon-remove',
                'cssClass' => 'btn-default',
            ],
        ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Inventory units export action.
     *
     * @return StreamedResponse
     */
    public function exportUnitsAction()
    {
        $repository = $this->get('ekyna_product.product_stock_unit.repository');

        $response = new StreamedResponse();

        $response->setCallback(function () use ($repository) {
            if (false === $handle = fopen('php://output', 'w+')) {
                throw new \RuntimeException("Failed to open output stream.");
            }

            $stockUnits = $repository->findInStock();

            fputcsv($handle, [
                'id',
                'designation',
                'reference',
                'stock',
                'buy price',
                'geocode',
            ], ';', '"');

            /** @var \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface $stockUnit */
            foreach ($stockUnits as $stockUnit) {
                $inStock = $stockUnit->getReceivedQuantity()
                    + $stockUnit->getAdjustedQuantity()
                    - $stockUnit->getShippedQuantity();

                /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
                $product = $stockUnit->getSubject();

                $data = [
                    $product->getId(),
                    (string)$product,
                    $product->getReference(),
                    $inStock,
                    $stockUnit->getNetPrice(),
                    implode(', ', $stockUnit->getGeocodes()),
                ];

                fputcsv($handle, $data, ';', '"');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'inventory-units.csv'
        ));

        return $response;
    }

    /**
     * Inventory products export action.
     *
     * @return StreamedResponse
     */
    public function exportProductsAction()
    {
        $repository = $this->get('ekyna_product.product.repository');

        $response = new StreamedResponse();

        $response->setCallback(function () use ($repository) {
            if (false === $handle = fopen('php://output', 'w+')) {
                throw new \RuntimeException("Failed to open output stream.");
            }

            $products = $repository->findForInventoryExport();

            fputcsv($handle, [
                'id',
                'designation',
                'reference',
                'stock',
                'geocode',
            ], ';', '"');

            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
            foreach ($products as $product) {
                $data = [
                    $product->getId(),
                    $product->getFullDesignation(true),
                    $product->getReference(),
                    $product->getInStock(),
                    $product->getGeocode(),
                ];

                fputcsv($handle, $data, ';', '"');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'inventory-products.csv'
        ));

        return $response;
    }

    /**
     * Finds the product by request.
     *
     * @param Request $request
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface
     */
    private function findProductByRequest(Request $request)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $product */
        $product = $this
            ->get('ekyna_product.product.repository')
            ->find($request->attributes->get('productId'));

        if (null === $product) {
            throw $this->createNotFoundException('Product not found.');
        }

        return $product;
    }
}
