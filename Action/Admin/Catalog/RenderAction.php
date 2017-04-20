<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Catalog;

use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class RenderAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Catalog
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenderAction extends AbstractRenderAction
{
    public function __invoke(): Response
    {
        $catalog = $this->context->getResource();
        if (!$catalog instanceof CatalogInterface) {
            throw new UnexpectedTypeException($catalog, CatalogInterface::class);
        }

        $catalog
            ->setContext(
                $this->contextProvider->getContext()
            )
            ->setDisplayPrices(true)
            ->setFormat(CatalogRenderer::FORMAT_PDF);

        $form = $this->createRenderForm($catalog);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this
                    ->catalogRenderer
                    ->respond($catalog, $this->request);
            } catch (PdfException $e) {
                $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

                return $this->redirect($this->generateResourcePath($catalog));
            }
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
        ])->setPrivate();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_catalog_render',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_render',
                'path'     => '/render',
                'methods'  => ['GET', 'POST'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'catalog.button.render',
                'trans_domain' => 'EkynaProduct',
                'theme'        => 'primary',
                'icon'         => 'file',
            ],
            'options'    => [
                'template' => '@EkynaProduct/Admin/Catalog/render.html.twig',
            ],
        ];
    }
}
