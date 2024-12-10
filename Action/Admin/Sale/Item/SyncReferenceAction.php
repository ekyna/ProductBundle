<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Bundle\ProductBundle\Model\Permission;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SyncReferenceAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SyncReferenceAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use XhrTrait;

    public function __construct(
        private readonly ItemBuilder $itemBuilder
    ) {
    }

    public function __invoke(): Response
    {
        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }

        try {
            if ($this->itemBuilder->buildReferences($item)) {
                $this->getManager()->update($item);
            }
        } catch (IllegalOperationException) {
            // TODO Add error message to sale view
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getRootSale());
        }

        return $this->redirectToReferer($this->generateResourcePath($item->getRootSale()));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_sale_item_sync_reference',
            'permission' => Permission::SYNC_REFERENCE,
            'route'      => [
                'name'     => 'admin_%s_sync_reference',
                'path'     => '/sync-reference',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'sale_item.button.sync_reference',
                'trans_domain' => 'EkynaProduct',
                'theme'        => 'primary',
                'icon'         => 'fa fa-hashtag',
            ],
        ];
    }
}
