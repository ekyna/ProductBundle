<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\Sale;

use Ekyna\Bundle\ProductBundle\Model\CategoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\CategoryRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ListProductController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ListProductController
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductRepositoryInterface  $productRepository,
        private readonly SerializerInterface         $serializer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $category = $this->categoryRepository->find($request->attributes->getInt('categoryId'));
        if (!$category instanceof CategoryInterface) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $products = $this->productRepository->findForSaleBrowse($category, true);

        $normalized = $this->serializer->normalize([
            'category' => $category,
            'products' => $products,
        ], 'json', ['groups' => ['Sale']]);

        return new JsonResponse($normalized);
    }
}
