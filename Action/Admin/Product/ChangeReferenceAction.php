<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Form\Type\ChangeReferenceType;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use \Ekyna\Bundle\ProductBundle\Model\Permission;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ChangeReferenceAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ChangeReferenceAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use HelperTrait;
    use ManagerTrait;
    use TemplatingTrait;
    use BreadcrumbTrait;

    public function __invoke(): Response
    {
        $product = $this->context->getResource();
        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        $product->addReferenceAlias($product->getReference());

        $form = $this->createForm(ChangeReferenceType::class, $product);

        FormUtil::addFooter($form, [
            'cancel_path' => $this->generateResourcePath($this->context->getResource()),
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getManager()->save($product);

            return new RedirectResponse(
                $this->generateResourcePath($product)
            );
        }

        $this->breadcrumbFromContext($this->context);

        $config = $this->context->getConfig();

        $content = $this->twig->render('@EkynaProduct/Admin/Product/change_reference.html.twig', [
            'context'                   => $this->context,
            $config->getCamelCaseName() => $this->context->getResource(),
            'form'                      => $form->createView(),
        ]);

        return new Response($content);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_change_reference',
            'permission' => Permission::CHANGE_REFERENCE,
            'route'      => [
                'name'     => 'admin_%s_change_reference',
                'path'     => '/change-reference',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'product.button.change_reference',
                'trans_domain' => 'EkynaProduct',
                'theme'        => 'default',
                'icon'         => 'fa fa-tags',
            ],
        ];
    }
}
