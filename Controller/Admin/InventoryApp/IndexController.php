<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\InventoryApp;

use Ekyna\Bundle\ProductBundle\Repository\InventoryRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class IndexController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IndexController
{
    public function __construct(
        private readonly InventoryRepositoryInterface $inventoryRepository,
        private readonly Environment                  $twig,
    ) {
    }

    public function __invoke(): Response
    {
        if (null === $this->inventoryRepository->findOneOpened()) {
            $content = $this->twig->render('@EkynaProduct/Inventory/closed.html.twig');
        } else {
            $content = $this->twig->render('@EkynaProduct/Inventory/index.html.twig');
        }

        return (new Response($content))->setPrivate();
    }
}
