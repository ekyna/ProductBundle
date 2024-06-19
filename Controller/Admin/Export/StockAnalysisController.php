<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\Export;

use Ekyna\Bundle\ProductBundle\Service\Stock\AnalysisExporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StockAnalysisController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockAnalysisController
{
    public function __construct(
        private readonly AnalysisExporter $exporter,
    ) {
    }

    public function __invoke(): Response
    {
        $path = $this->exporter->exportXls();

        return new BinaryFileResponse($path);
    }
}
