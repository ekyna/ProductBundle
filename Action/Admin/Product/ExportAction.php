<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\ProductBundle\Exception\ProductExceptionInterface;
use Ekyna\Bundle\ProductBundle\Form\Type\ExportConfigType;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Service\Exporter\ProductExporter;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Helper\File\Csv;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Symfony\Component\Translation\t;

/**
 * Class ExportAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportAction extends AbstractAction implements AdminActionInterface
{
    use FormTrait;
    use HelperTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    private ContextProviderInterface $contextProvider;
    private ProductExporter          $productExporter;

    public function __construct(ContextProviderInterface $contextProvider, ProductExporter $productExporter)
    {
        $this->contextProvider = $contextProvider;
        $this->productExporter = $productExporter;
    }

    public function __invoke(): Response
    {
        if ($this->context->getConfig()->getEntityInterface() !== ProductInterface::class) {
            throw new NotFoundHttpException();
        }

        $config = new ExportConfig(
            $this->contextProvider->getContext()
        );

        $action = $this->generateResourcePath(ProductInterface::class, self::class);
        $cancel = $this->generateResourcePath(ProductInterface::class, ListAction::class);

        $form = $this->createForm($this->options['type'], $config, [
            'action'            => $action,
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal'],
            '_redirect_enabled' => true,
        ]);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.export', [], 'EkynaUi'),
            'submit_class' => 'success',
            'cancel_path'  => $cancel,
        ]);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $this->productExporter->export($config);

                return $file->download();
            } catch (ProductExceptionInterface $e) {
                $this->addFlash($e->getMessage(), 'danger');
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
            'name'       => 'product_product_export',
            'permission' => Permission::READ,
            'route'      => [
                'name'    => 'admin_%s_export',
                'path'    => '/export',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.export',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'download',
            ],
            'options'    => [
                'template' => '@EkynaProduct/Admin/Product/export.html.twig',
                'type'     => ExportConfigType::class,
            ],
        ];
    }
}
