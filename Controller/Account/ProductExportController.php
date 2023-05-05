<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Controller\Account\CustomerTrait;
use Ekyna\Bundle\ProductBundle\Model\ExportConfig;
use Ekyna\Bundle\ProductBundle\Service\Exporter\ProductExporter;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Symfony\Component\HttpFoundation\Response;

use function date;
use function sprintf;

/**
 * Class ProductExportController
 * @package Ekyna\Bundle\ProductBundle\Controller\Account
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductExportController implements ControllerInterface
{
    use CustomerTrait;

    public function __construct(
        private readonly ContextProviderInterface $contextProvider,
        private readonly ProductExporter          $productExporter,
    ) {
    }

    public function __invoke(): Response
    {
        $customer = $this->getCustomer();

        $context = $this->contextProvider->getContext();

        $config = new ExportConfig($context);

        $csv = $this->productExporter->export($config);

        return $csv->download([
            'file_name' => sprintf('%s_pricing_%s.csv', $customer->getNumber(), date('Y-m-d')),
        ]);
    }
}
