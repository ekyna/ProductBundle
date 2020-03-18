<?php

namespace Ekyna\Bundle\ProductBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Menu\MenuBuilder;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Bundle\ProductBundle\Model\HighlightModes;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class HighlightController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HighlightController extends Controller
{
    /**
     * Highlight action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this
            ->container
            ->get(MenuBuilder::class)
            ->breadcrumbAppend(
                'ekyna_product_highlight',
                'ekyna_product.highlight.title',
                'ekyna_product_highlight_admin_index'
            );

        $qb = $this
            ->get('ekyna_product.product.repository')
            ->createQueryBuilder('p');

        $products = $qb
            ->select([
                'p.id',
                'b.name as brand',
                'p.designation',
                'p.reference',
                'p.visible',
                'p.visibility',
                'p.bestSeller',
                'p.crossSelling',
            ])
            ->join('p.brand', 'b')
            ->where($qb->expr()->neq('p.type', ':not_type'))
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->setParameter('not_type', ProductTypes::TYPE_VARIANT)
            ->getScalarResult();

        $translator = $this->getTranslator();

        $visibleChoices = [
            $translator->trans('ekyna_core.value.no')  => 0,
            $translator->trans('ekyna_core.value.yes') => 1,
        ];

        $highlightChoices = [];
        foreach (HighlightModes::getChoices() as $key => $value) {
            $highlightChoices[$translator->trans($key)] = $value;
        }

        return $this->render('@EkynaProduct/Admin/Highlight/index.html.twig', [
            'products'          => $products,
            'visible_choices'   => $visibleChoices,
            'highlight_choices' => $highlightChoices,
        ]);
    }

    /**
     * Updates the product.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $productId = $request->attributes->get('productId');
        $property = $request->request->get('property');
        $value = $request->request->get('value');

        if (empty($productId) || empty($property) || is_null($value)) {
            throw new BadRequestHttpException("Id, property and message must be defined with non empty values.");
        }

        $product = $this->get('ekyna_product.product.repository')->find($productId);
        if (null === $product) {
            throw $this->createNotFoundException('Product not found.');
        }

        switch ($property) {
            case 'visible':
                $product->setVisible((bool)$value);
                break;
            case 'visibility':
                $product->setVisibility((int)$value);
                break;
            case 'bestSeller':
                if (!HighlightModes::isValid($value)) {
                    throw new BadRequestHttpException("Unexpected value '$value'.");
                }
                $product->setBestSeller($value);
                break;
            case 'crossSelling':
                if (!HighlightModes::isValid($value)) {
                    throw new BadRequestHttpException("Unexpected value '$value'.");
                }
                $product->setCrossSelling($value);
                break;
            default:
                throw new BadRequestHttpException("Unexpected property '$property'.");
        }

        $violations = $this->getValidator()->validate($product, null, ['Default', $product->getType()]);
        if (0 < $violations->count()) {
            throw new BadRequestHttpException("Product is not valid.");
        }

        $em = $this->get('ekyna_product.product.manager');
        $em->persist($product);
        $em->flush();

        return JsonResponse::create([
            'id'           => $product->getId(),
            'visible'      => $product->isVisible() ? '1' : '0',
            'visibility'   => $product->getVisibility(),
            'bestSeller'   => $product->getBestSeller(),
            'crossSelling' => $product->getCrossSelling(),
        ]);
    }
}
