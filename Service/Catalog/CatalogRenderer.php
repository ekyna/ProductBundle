<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Catalog;

use Behat\Transliterator\Transliterator;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Entity\CatalogSlot;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Helper\PdfGenerator;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

/**
 * Class CatalogRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogRenderer
{
    public const FORMAT_PDF   = 'PDF';
    public const FORMAT_HTML  = 'HTML';
    public const FORMAT_EMAIL = 'EMail';

    protected CatalogRegistry $registry;
    protected Environment $twig;
    protected PdfGenerator $pdfGenerator;
    protected SubjectHelperInterface $subjectHelper;
    protected string $logoPath;
    protected bool $debug;

    public function __construct(
        CatalogRegistry        $registry,
        Environment            $twig,
        PdfGenerator           $pdfGenerator,
        SubjectHelperInterface $subjectHelper,
        string                 $logoPath,
        bool                   $debug = false
    ) {
        $this->registry = $registry;
        $this->twig = $twig;
        $this->pdfGenerator = $pdfGenerator;
        $this->subjectHelper = $subjectHelper;
        $this->logoPath = $logoPath;
        $this->debug = $debug;
    }

    /**
     * Returns the catalog response.
     *
     * @throws PdfException
     */
    public function respond(CatalogInterface $catalog, Request $request = null): Response
    {
        $response = new Response();

        $format = $catalog->getFormat();
        $download = $request && $request->query->getBoolean('download', false);

        if ($format === static::FORMAT_PDF) {
            $response->headers->add(['Content-Type' => 'application/pdf']);
            $extension = 'pdf';
        } else {
            $extension = 'html';
        }

        $disposition = $download
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $filename = sprintf('%s.%s', Transliterator::urlize($catalog->getTitle()), $extension);

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition($disposition, $filename)
        );

        if ($request && !$this->debug) {
            $response->setLastModified($catalog->getUpdatedAt());
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $response->setContent($this->render($catalog));

        return $response;
    }

    /**
     * Renders the catalog.
     *
     * @throws PdfException
     */
    public function render(CatalogInterface $catalog): string
    {
        $template = 'render';
        $theme = $this->registry->getTheme($catalog->getTheme())['path'];

        if (0 === $catalog->getPages()->count()) {
            $this->buildPages($catalog);
        }

        $content = $this->twig->render('@EkynaProduct/Catalog/render.html.twig', [
            'catalog'   => $catalog,
            'theme'     => $theme,
            'template'  => $template,
            'logo_path' => $this->logoPath,
        ]);

        if ($catalog->getFormat() === static::FORMAT_PDF) {
            $options = [
                'margins' => [
                    'top'    => 0,
                    'right'  => 0,
                    'bottom' => 0,
                    'left'   => 0,
                ],
            ];

            return $this->pdfGenerator->generateFromHtml($content, $options);
        }

        return $content;
    }

    /**
     * Builds the catalog pages from sale items list.
     */
    private function buildPages(CatalogInterface $catalog): void
    {
        $products = [];

        foreach ($catalog->getSaleItems() as $item) {
            $product = $this->subjectHelper->resolve($item);

            if (!$product instanceof ProductInterface) {
                continue;
            }

            $products[] = $product;
        }

        if (empty($products)) {
            throw new LogicException('No product found.');
        }

        $max = $this->registry->getTemplate($catalog->getTemplate())['slots'];
        if (0 >= $max) {
            throw new LogicException('Template does not have products slots.');
        }

        $count = 0;
        $page = null;
        foreach ($products as $product) {
            if ($count % $max === 0) {
                $page = new CatalogPage();
                $page->setTemplate($catalog->getTemplate());
                $catalog->addPage($page);
            }

            $slot = new CatalogSlot();
            $slot->setProduct($product);
            $page->addSlot($slot);

            $count++;
        }
    }

    /**
     * Validates the format.
     */
    protected function validateFormat(string $format): void
    {
        if (!in_array($format, static::getFormats(), true)) {
            throw new InvalidArgumentException("Unsupported format '$format'.");
        }
    }

    /**
     * Returns the formats
     */
    public static function getFormats(): array
    {
        return [
            static::FORMAT_PDF   => static::FORMAT_PDF,
            static::FORMAT_HTML  => static::FORMAT_HTML,
            static::FORMAT_EMAIL => static::FORMAT_EMAIL,
        ];
    }
}
