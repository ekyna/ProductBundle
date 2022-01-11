<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Factory\ProductFactoryInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Routing\Route;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction implements RoutingActionInterface
{
    use RepositoryTrait;

    protected function createResource(): ResourceInterface
    {
        $factory = $this->getFactory();

        if (!$factory instanceof ProductFactoryInterface) {
            throw new UnexpectedTypeException($factory, ProductFactoryInterface::class);
        }

        $type = $this->request->attributes->get('type');

        $product = $factory->createWithType($type);

        $this->setProductParent($product);

        return $product;
    }

    private function setProductParent(ProductInterface $product): void
    {
        if (0 >= $parent = $this->request->query->getInt('parent')) {
            return;
        }

        $parent = $this->getRepository()->find($parent);

        if (!$parent instanceof ProductInterface) {
            throw new UnexpectedTypeException($parent, ProductInterface::class);
        }

        if (!ProductTypes::isVariableType($parent)) {
            throw new LogicException('Parent product must be of \'variable\' type.');
        }

        $product->setParent($parent);
    }

    protected function getFormOptions(): array
    {
        return array_replace(parent::getFormOptions(), [
            'action' => $this->generateResourcePath('ekyna_product.product', self::class, [
                'type' => $this->request->attributes->get('type'),
            ]),
        ]);
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'product_product_create',
            'route'   => [
                'path' => '/create/{type}',
            ],
            'button'  => [
                'label'        => 'product.button.new',
                'trans_domain' => 'EkynaProduct',
            ],
            'options' => [
                'template'      => '@EkynaProduct/Admin/Product/create.html.twig',
                'form_template' => '@EkynaProduct/Admin/Product/_form.html.twig',
            ],
        ]);
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'type' => '^[a-z]+$',
        ]);
    }
}
