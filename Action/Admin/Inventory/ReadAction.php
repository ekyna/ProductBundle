<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Inventory;

use Ekyna\Bundle\AdminBundle\Action\ReadAction as BaseAction;
use Ekyna\Bundle\ProductBundle\Entity\Inventory;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Repository\InventoryProductRepository;

/**
 * Class ReadAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReadAction extends BaseAction
{
    public function __construct(
        private readonly InventoryProductRepository $repository
    ) {
    }

    protected function buildParameters(): array
    {
        $inventory = $this->context->getResource();
        if (!$inventory instanceof Inventory) {
            throw new UnexpectedTypeException($inventory, Inventory::class);
        }

        $products = $this->repository->findByInventoryWithRealStock($inventory);

        $parameters = parent::buildParameters();

        $parameters['products'] = $products;

        return $parameters;
    }

    public static function configureAction(): array
    {
        return array_replace(parent::configureAction(), [
            'name'    => 'product_inventory_read',
            'options' => [
                'template' => '@EkynaProduct/Admin/Inventory/read.html.twig',
            ],
        ]);
    }
}
