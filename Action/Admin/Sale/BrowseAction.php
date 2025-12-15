<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Sale;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\ModalTrait;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item\AddSubjectAction;
use Ekyna\Bundle\ProductBundle\Repository\CategoryRepositoryInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BrowseAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BrowseAction extends AbstractAction implements AdminActionInterface
{
    use TemplatingTrait;
    use ModalTrait;

    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ResourceHelper $resourceHelper,
    ) {
    }

    public function __invoke(): Response
    {
        $sale = $this->context->getResource();

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        $categories = $this->categoryRepository->findForSaleBrowse();

        $config = $this->resourceHelper->getResourceConfig($sale);
        $addUrl = $this->resourceHelper->generateResourcePath($config->getId() . '_item', AddSubjectAction::class, [
            $config->getName() . 'Id' => $sale->getId(),
        ], true);

        $content = $this->renderView('@EkynaProduct/Admin/Sale/browse.html.twig', [
            'categories'    => $categories,
            'add_item_path' => $addUrl,
        ]);

        if (!$this->request->isXmlHttpRequest()) {
            return new Response($content);
        }

        $modal = new Modal();
        $modal->setTitle('Ajout produit');;
        $modal->setType(Modal::TYPE_PRIMARY);
        $modal->setSize(Modal::SIZE_WIDE);
        $modal->setHtml($content);

        return $this->renderModal($modal);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_sale_browse',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_browse',
                'path'     => '/add-product',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'sale.button.browse',
                'trans_domain' => 'EkynaProduct',
                'theme'        => 'primary',
                'icon'         => 'fa fa-package',
            ],
        ];
    }
}
