<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Inventory;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\CreateAction as Create;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\InventoryRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Service\Inventory\InventoryGenerator;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function array_replace;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends AbstractAction implements AdminActionInterface
{
    use FlashTrait;
    use HelperTrait;
    use FactoryTrait;
    use ManagerTrait;

    public function __construct(
        private readonly InventoryRepositoryInterface $repository,
        private readonly InventoryGenerator           $generator,
    ) {
    }

    public function __invoke(): Response
    {
        $redirect = new RedirectResponse($this->getRedirectPath());

        if ($this->repository->findOneNotClosed()) {
            $this->addFlash('Please close all other inventories before.', 'warning');

            return $redirect;
        }

        $inventory = $this->createResource();
        if (!$inventory instanceof InventoryInterface) {
            throw new UnexpectedTypeException($inventory, InventoryInterface::class);
        }

        $this->generator->generate($inventory);

        $this->getManager()->save($inventory);

        return $redirect;
    }

    private function getRedirectPath(): string
    {
        $path = $this->generateResourcePath(InventoryInterface::class, ListAction::class);

        $referer = $this->request->headers->get('referer');
        if (!empty($referer) && !str_contains($referer, $path)) {
            return $referer;
        }

        return $path;
    }

    public static function configureAction(): array
    {
        return array_replace(Create::configureAction(), [
            'name' => 'product_inventory_create',
        ]);
    }
}
