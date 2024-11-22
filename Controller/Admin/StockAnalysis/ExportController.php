<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Controller\Admin\StockAnalysis;

use Ekyna\Bundle\ProductBundle\Service\Stock\Analysis\Exporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use function pathinfo;

use const PATHINFO_BASENAME;

/**
 * Class ExportController
 * @package Ekyna\Bundle\ProductBundle\Controller\Admin\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ExportController
{
    public function __construct(
        private readonly Exporter $exporter,
    ) {
    }

    public function __invoke(): Response
    {
        $path = $this->exporter->exportXls();

        $response = new BinaryFileResponse($path);

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            pathinfo($path, PATHINFO_BASENAME)
        );

        return $response;
    }
}
