<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp;

use Ekyna\Bundle\ProductBundle\Repository\InventoryProductRepository;
use Ekyna\Bundle\ProductBundle\Repository\InventoryRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ListController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ListController
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository,
        private readonly InventoryProductRepository $inventoryProductRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function __invoke(): Response
    {
        if (null === $inventory = $this->inventoryRepository->findOneOpened()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $products = $this->inventoryProductRepository->findByInventory($inventory);

        $json = $this->serializer->serialize([
            'products' => $products,
        ], 'json');

        return JsonResponse::fromJsonString($json);
    }
}
