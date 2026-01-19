<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockView;

use Ekyna\Bundle\ProductBundle\Service\Exporter\StockExporter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ValuationExportController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\StockView
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ValuationExportController
{
    public function __construct(
        private readonly StockExporter $exporter
    ) {
    }

    public function __invoke(): Response
    {
        return $this->exporter->export()->download();
    }
}
