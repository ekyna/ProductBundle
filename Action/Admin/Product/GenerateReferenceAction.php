<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ProductBundle\Exception\ProductExceptionInterface;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ProductBundle\Service\Generator\ExternalReferenceGenerator;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Class GenerateReferenceAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GenerateReferenceAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use FlashTrait;
    use ManagerTrait;
    use HelperTrait;

    private ExternalReferenceGenerator $generator;

    public function __construct(ExternalReferenceGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function __invoke(): Response
    {
        $product = $this->context->getResource();
        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        $type = $this->request->attributes->get('type');

        switch ($type) {
            case ProductReferenceTypes::TYPE_EAN_13:
                try {
                    $this->generator->generateGtin13($product);
                } catch (ProductExceptionInterface $exception) {
                    $this->addFlash($exception->getMessage(), 'danger');
                }

                $event = $this->getManager()->save($product);

                $this->addFlashFromEvent($event);

                break;

            default:
                throw new RuntimeException('Unsupported reference type.');
        }

        return $this->redirect($this->generateResourcePath($product));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_generate_reference',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'    => 'admin_%s_generate_reference',
                'path'    => '/generate-reference/{type}',
                'methods' => ['GET'],
                'resource' => true,
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'type' => '[a-z0-9]+',
        ]);
    }
}
