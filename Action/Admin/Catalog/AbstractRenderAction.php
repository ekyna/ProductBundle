<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Catalog;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Form\Type\Catalog\CatalogRenderType;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRenderer;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\FactoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function file_put_contents;
use function get_class;
use function Symfony\Component\Translation\t;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * Class AbstractRenderAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Catalog
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractRenderAction extends AbstractAction implements AdminActionInterface
{
    use FactoryTrait;
    use RepositoryTrait;
    use ManagerTrait;
    use HelperTrait;
    use FormTrait;
    use FlashTrait;
    use BreadcrumbTrait;
    use TemplatingTrait;

    protected ContextProviderInterface $contextProvider;
    protected CatalogRenderer          $catalogRenderer;
    protected SaleFactoryInterface     $saleFactory;

    public function __construct(
        ContextProviderInterface $contextProvider,
        CatalogRenderer          $catalogRenderer,
        SaleFactoryInterface     $saleFactory
    ) {
        $this->contextProvider = $contextProvider;
        $this->catalogRenderer = $catalogRenderer;
        $this->saleFactory = $saleFactory;
    }

    protected function createRenderForm(CatalogInterface $catalog, SaleInterface $sale = null): FormInterface
    {
        if ($sale) {
            $action = $this->generateResourcePath($catalog, RenderFromSaleAction::class, [
                'type' => $this->request->attributes->get('type'),
                'id'   => $sale->getId(),
            ]);
            $cancel = $this->generateResourcePath($sale);
        } else {
            $action = $this->generateResourcePath($catalog, RenderAction::class);
            $cancel = $this->generateResourcePath($catalog);
        }

        $form = $this->createForm(CatalogRenderType::class, $catalog, [
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            '_redirect_enabled' => true,
            'sale'              => $sale,
            'validation_groups' => ['CatalogFromSale'],
        ]);

        FormUtil::addFooter($form, [
            'submit_label' => t('button.display', [], 'EkynaUi'),
            'cancel_path'  => $cancel,
        ]);

        return $form;
    }

    /**
     * Saves the catalog as a sale attachment.
     */
    protected function saveSaleCatalog(SaleInterface $sale, string $content): bool
    {
        $path = sys_get_temp_dir() . '/' . uniqid() . '.pdf';
        if (!file_put_contents($path, $content)) {
            throw new RuntimeException("Failed to write content into file '$path'.");
        }

        // Fake uploaded file
        $file = new UploadedFile($path, 'catalog.pdf', null, null, true);

        // Attachment
        $attachment = $this->saleFactory->createAttachmentForSale($sale);

        $attachment
            ->setTitle('Catalog')
            ->setFile($file);

        $sale->addAttachment($attachment);

        $event = $this
            ->getManager(get_class($attachment))
            ->save($attachment);

        $this->addFlashFromEvent($event);

        unlink($path);

        return !$event->hasErrors();
    }
}
