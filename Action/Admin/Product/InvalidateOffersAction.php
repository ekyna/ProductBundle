<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Pricing\OfferInvalidator;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InvalidateOffersAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvalidateOffersAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;

    public function __construct(private readonly OfferInvalidator $offerInvalidator)
    {
    }

    public function __invoke(): Response
    {
        $product = $this->context->getResource();
        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        $this->offerInvalidator->invalidateByProduct($product);
        $this->offerInvalidator->flush();

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
