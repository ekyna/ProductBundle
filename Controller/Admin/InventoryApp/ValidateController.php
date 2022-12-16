<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp;

use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ValidateController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ValidateController extends AbstractProductController
{
    public function __invoke(Request $request): Response
    {
        if (null === $product = $this->getProduct($request)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $this->validate($product);

        return $this->respond($product);
    }

    private function validate(InventoryProduct $product): void
    {
        $product->setRealStock(clone $product->getInitialStock());

        $this->manager->persist($product);
        $this->manager->flush();
    }
}
