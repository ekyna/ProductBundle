<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Attribute;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use function array_replace;
use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Attribute
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction implements RoutingActionInterface
{
    private AttributeTypeRegistryInterface $typeRegistry;

    public function __construct(AttributeTypeRegistryInterface $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    protected function onInit(): ?Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof AttributeInterface) {
            throw new UnexpectedTypeException($resource, AttributeInterface::class);
        }

        if (!$type = $this->request->attributes->getAlpha('type')) {
            return null;
        }

        if (!$this->typeRegistry->hasType($type)) {
            throw new InvalidArgumentException("Unknown type '$type'.");
        }

        $resource->setType($type);

        return parent::onInit();
    }

    protected function getFormOptions(): array
    {
        $action = $this->generateResourcePath(
            $this->context->getResource(),
            static::class,
            array_replace($this->request->query->all(), [
                'type' => $this->request->attributes->getAlpha('type'),
            ]),
        );

        return [
            'action' => $action,
        ];
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'product_attribute_create',
            'route'   => [
                'path' => '/create/{type}',
            ],
            'options' => [
                'template'      => '@EkynaProduct/Admin/Attribute/create.html.twig',
                'form_template' => '@EkynaProduct/Admin/Attribute/_form.html.twig',
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
