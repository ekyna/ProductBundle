<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractController
{
    protected ProductRepositoryInterface $productRepository;
    protected ModalRenderer              $modalRenderer;
    protected bool                       $debug;

    public function init(
        ProductRepositoryInterface $productRepository,
        ModalRenderer              $modalRenderer,
        bool                       $debug
    ): void {
        $this->productRepository = $productRepository;
        $this->modalRenderer = $modalRenderer;
        $this->debug = $debug;
    }

    protected function findProductById(int $id): ProductInterface
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException('Product not found.');
        }

        return $product;
    }

    /**
     * @param array<int> $ids
     *
     * @return array<ProductInterface>
     */
    protected function findProductsById(array $ids): array
    {
        return $this->productRepository->findBy(['id' => $ids]);
    }

    protected function assertXhr(Request $request): void
    {
        if (!$request->isXmlHttpRequest() && !$this->debug) {
            throw new NotFoundHttpException('Not yet implemented. Only XHR is supported.');
        }
    }
}
