<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Converter\ProductConverter;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class ConvertAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use TemplatingTrait;
    use FlashTrait;
    use HelperTrait;
    use BreadcrumbTrait;

    private ProductConverter $converter;

    public function __construct(ProductConverter $converter)
    {
        $this->converter = $converter;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof ProductInterface) {
            throw new UnexpectedTypeException($resource, ProductInterface::class);
        }

        $sourceType = $resource->getType();
        $targetType = $this->request->attributes->get('type');

        if (!$this->converter->can($resource, $targetType)) {
            throw new NotFoundHttpException('Not yet implemented.');
        }

        $event = $this->converter->convert($resource, $targetType);

        if (null === $form = $event->getForm()) {
            $this->addFlashFromEvent($event);

            if ($event->isSuccess()) {
                $this->addFlash(t('convert.success', [], 'EkynaProduct'), 'success');

                return $this->redirect($this->generateResourcePath($event->getTarget()));
            }

            return $this->redirect($this->generateResourcePath($resource));
        }

        $formTemplate = sprintf(
            '@EkynaProduct/Admin/Product/Convert/_%s_to_%s_form.html.twig',
            $sourceType, $targetType
        );

        FormUtil::addErrorsFromResourceEvent($form, $event);

        $this->breadcrumbFromContext($this->context);

        $content = $this->twig->render($this->options['template'], [
            'context'       => $this->context,
            'form'          => $form->createView(),
            'form_template' => $formTemplate,
        ]);

        return (new Response($content))->setPrivate();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_convert',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_convert',
                'path'     => '/convert/{type}',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.convert', // TODO ?
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'duplicate',
            ],
            'options'    => [
                'template' => '@EkynaProduct/Admin/Product/convert.html.twig',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'type' => '^[a-z]+$',
        ]);
    }
}
