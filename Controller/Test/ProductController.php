<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Test;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductController
 * @package Ekyna\Bundle\ProductBundle\Controller\Test
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductController extends Controller
{
    /**
     * Product detail action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function detailAction(Request $request)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Entity\Product $product */
        $product = $this
            ->get('ekyna_product.product.repository')
            ->findOneByReference($request->attributes->get('reference'));

        if (null === $product) {
            throw $this->createNotFoundException("Product not found.");
        }

        // Add to cart form
        $cartHelper = $this->get('ekyna_commerce.cart_helper');
        $form = $cartHelper
            ->createAddSubjectToCartForm($product, [
                'extended'      => false,
                'submit_button' => true,
            ]);

        if (null !== $event = $cartHelper->handleAddSubjectToCartForm($form, $request)) {
            $this->addFlash($event->getMessage(), 'info');

            return $this->redirectToRoute('ekyna_product_front_product_detail', [
                'reference' => $product->getReference(),
            ]);
        }

        // TODO Resource locale switcher

        $view = $form->createView();

        return $this->render('@EkynaProduct/Test/Product/detail.html.twig', [
            'product' => $product,
            'form'    => $view,
        ]);
    }

    public function dumpAction($productId)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Entity\Product $product */
        $product = $this->get('ekyna_product.product.repository')->find($productId);

        if (null === $product) {
            throw $this->createNotFoundException("Product not found.");
        }

        return $this->render('@EkynaProduct/Test/Product/dump.html.twig', [
            'product' => $product,
        ]);
    }
}
