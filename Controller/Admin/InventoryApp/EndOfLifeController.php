<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EndOfLifeController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EndOfLifeController extends AbstractProductController
{
    public function __invoke(Request $request): Response
    {
        if (null === $product = $this->getProduct($request)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $this->setEndOfLife($product->getProduct());

        return $this->respond($product);
    }

    private function setEndOfLife(ProductInterface $product): void
    {
        $product->setEndOfLife(true);

        $this->manager->persist($product);
        $this->manager->flush();
    }
}
