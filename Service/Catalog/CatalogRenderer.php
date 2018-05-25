<?php

namespace Ekyna\Bundle\ProductBundle\Service\Catalog;

use Behat\Transliterator\Transliterator;
use Ekyna\Bundle\ProductBundle\Entity\Catalog;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class CatalogRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Catalog
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogRenderer
{
    const FORMAT_PDF   = 'PDF';
    const FORMAT_HTML  = 'HTML';
    const FORMAT_EMAIL = 'EMail';

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var GeneratorInterface
     */
    protected $pdfGenerator;

    /**
     * @var string
     */
    protected $logoPath;

    /**
     * @var bool
     */
    protected $debug;


    /**
     * Constructor.
     *
     * @param EngineInterface    $templating
     * @param GeneratorInterface $pdfGenerator
     * @param string             $logoPath
     * @param bool               $debug
     */
    public function __construct(
        EngineInterface $templating,
        GeneratorInterface $pdfGenerator,
        $logoPath,
        $debug = false
    ) {
        $this->templating = $templating;
        $this->pdfGenerator = $pdfGenerator;
        $this->logoPath = $logoPath;
        $this->debug = $debug;
    }

    /**
     * Renders the catalog.
     *
     * @param Catalog $catalog
     *
     * @return string
     */
    public function render(Catalog $catalog)
    {
        $template = 'render';
        // TODO $template = $catalog->getFormat() === static::FORMAT_EMAIL ? 'email' : 'render';

        $content = $this->templating->render('@EkynaProduct/Catalog/render.html.twig', [
            'catalog'   => $catalog,
            'theme'     => '@EkynaProduct/Catalog/Theme/Default.html.twig',
            'template'  => $template,
            'logo_path' => $this->logoPath,
        ]);

        if ($catalog->getFormat() === static::FORMAT_PDF) {
            $options = [
                'margin-top'    => "0",
                'margin-right'  => "0",
                'margin-bottom' => "0",
                'margin-left'   => "0",
            ];

            return $this->pdfGenerator->getOutputFromHtml($content, $options);
        }

        return $content;
    }

    /**
     * Returns the catalog response.
     *
     * @param Catalog $catalog
     * @param Request $request
     *
     * @return string
     */
    public function respond(Catalog $catalog, Request $request = null)
    {
        $response = new Response();

        $format = $catalog->getFormat();
        $download = $request ? (bool)$request->query->get('download', 0) : false;

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
     * Validates the format.
     *
     * @param string $format
     */
    protected function validateFormat($format)
    {
        if (!in_array($format, static::getFormats(), true)) {
            throw new InvalidArgumentException("Unsupported format '$format'.");
        }
    }

    /**
     * Returns the formats
     *
     * @return array
     */
    public static function getFormats()
    {
        return [
            static::FORMAT_PDF   => static::FORMAT_PDF,
            static::FORMAT_HTML  => static::FORMAT_HTML,
            static::FORMAT_EMAIL => static::FORMAT_EMAIL,
        ];
    }
}
