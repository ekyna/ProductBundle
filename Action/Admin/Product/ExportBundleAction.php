<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Action\Admin\Product;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Exporter\BundleExporter;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BundleExportAction
 * @package Ekyna\Bundle\ProductBundle\Action\Admin\Product
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportBundleAction extends AbstractAction implements AdminActionInterface
{
    public function __construct(
        private readonly BundleExporter $bundleExporter,
    ) {
    }

    public function __invoke(): Response
    {
        $product = $this->context->getResource();

        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        if (!ProductTypes::isBundleType($product)) {
            throw new UnexpectedValueException('Expected bundle product type.');
        }

        $file = $this->bundleExporter->export($product, $this->request->query->getBoolean('recursive'));

        return $file->download();
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'product_product_export_bundle',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_export_bundle',
                'path'     => '/export-bundle',
                'methods'  => ['GET'],
                'resource' => true,
            ],
            'button'     => [
                'label'        => 'button.export',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'download',
            ],
        ];
    }
}
