<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Model\InventoryState;
use Ekyna\Bundle\ProductBundle\Repository\InventoryProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class AbstractProductController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractProductController
{
    public function __construct(
        protected readonly InventoryProductRepository $repository,
        protected readonly EntityManagerInterface     $manager,
        protected readonly SerializerInterface        $serializer,
    ) {
    }

    protected function respond(InventoryProduct $product): JsonResponse
    {
        $data = [
            'product' => $product,
        ];

        $data = $this->serializer->serialize($data, 'json');

        return JsonResponse::fromJsonString($data);
    }

    protected function getProduct(Request $request): ?InventoryProduct
    {
        if (0 >= $id = $request->attributes->getInt('id')) {
            return null;
        }

        if (null === $product = $this->repository->find($id)) {
            return null;
        }

        if (InventoryState::OPENED !== $product->getInventory()->getState()) {
            return null;
        }

        return $product;
    }
}
