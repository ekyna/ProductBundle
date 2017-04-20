<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Catalog;

use Ekyna\Bundle\CommerceBundle\Form\ChoiceList\SaleItemChoiceLoader;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;

use function Symfony\Component\Translation\t;

/**
 * Class RenderFromSaleAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Catalog
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenderFromSaleAction extends AbstractRenderAction implements RoutingActionInterface
{
    public function __invoke(): Response
    {
        $type = $this->request->attributes->getAlpha('type');
        $id = $this->request->attributes->getInt('id');

        /** @var SaleInterface $sale */
        if (null === $sale = $this->getRepository('ekyna_commerce.' . $type)->find($id)) {
            throw new NotFoundHttpException('Sale not found');
        }

        $loader = new SaleItemChoiceLoader($sale);

        /** @var CatalogInterface $catalog */
        $catalog = $this->getFactory(CatalogInterface::class)->create();
        $catalog
            ->setContext(
                $this->contextProvider->getContext($sale)
            )
            ->setDisplayPrices(false)
            ->setFormat(CatalogRenderer::FORMAT_PDF)
            ->setSaleItems($loader->loadItems());

        $form = $this->createRenderForm($catalog, $sale);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $response = $this
                    ->catalogRenderer
                    ->respond($catalog, $this->request);
            } catch (PdfException $e) {
                $this->addFlash(t('document.message.failed_to_generate', [], 'EkynaCommerce'), 'danger');

                return $this->redirect($this->generateResourcePath($sale));
            }

            if ($catalog->isSave()) {
                $this->saveSaleCatalog($sale, $response->getContent());

                return $this->redirect($this->generateResourcePath($sale));
            }

            return $response;
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'form'    => $form->createView(),
            'sale'    => $sale,
        ])->setPrivate();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_catalog_render_from_sale',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_render_from_sale',
                'path'    => '/render-from-sale/{type}/{id}',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'catalog.button.render_from_sale',
                'trans_domain' => 'EkynaProduct',
                'theme'        => 'default',
                'icon'         => 'file',
            ],
            'options'    => [
                'template' => '@EkynaProduct/Admin/Catalog/render_from_sale.html.twig',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'type' => 'cart|order|quote',
            'id'   => '\d+',
        ]);
    }
}
