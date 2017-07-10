<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Test;

use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->findOneById($request->attributes->get('productId'));

        if (null === $product) {
            throw $this->createNotFoundException("Product not found.");
        }

        // Add to cart form
        $cartHelper = $this->get('ekyna_commerce.cart_helper');
        $form = $cartHelper
            ->createAddSubjectToCartForm($product, [
                'extended' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
            ]);

        if (false !== $response = $cartHelper->handleAddSubjectToCartForm($form, $request)) {
            if ($response instanceof Response) {
                return $response->setPrivate();
            }

            $this->addFlash('ekyna_product.cart.message.success', 'success');

            return $this->redirectToRoute('app_product_detail', [
                'productSlug' => $product->getSlug(),
            ]);
        }

        $view = $form->createView();

        $response = $this->render('EkynaProductBundle:Test/Product:detail.html.twig', [
            'product' => $product,
            'form'    => $view,
        ]);

        return $this->configureSharedCache($response, $product->getEntityTags());
    }

    public function dumpAction($productId)
    {
        /** @var \Ekyna\Bundle\ProductBundle\Entity\Product $product */
        $product = $this->get('ekyna_product.product.repository')->find($productId);

        if (null === $product) {
            throw $this->createNotFoundException("Product not found.");
        }

        return $this->render('EkynaProductBundle:Test/Product:dump.html.twig', [
            'product' => $product,
        ]);
    }
}
