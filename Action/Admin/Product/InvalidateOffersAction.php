<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InvalidateOffersAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvalidateOffersAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use HelperTrait;
    use FlashTrait;

    public function __invoke(): Response
    {
        $product = $this->context->getResource();
        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        $product
            ->setPendingOffers(true)
            ->setPendingPrices(true);

        $event = $this->getManager()->update($product);

        $this->addFlashFromEvent($event);

        return $this->redirect($this->generateResourcePath($product));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_invalidate_offers',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'    => 'admin_%s_invalidate_offers',
                'path'    => '/invalidate-offers',
                'methods' => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'product.button.invalidate_offers',
                'trans_domain' => 'EkynaProduct',
                'theme'        => 'primary',
                'icon'         => 'refresh',
            ],
        ];
    }
}
