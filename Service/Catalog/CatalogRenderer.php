<?php

namespace Ekyna\Bundle\ProductBundle\Service\Catalog;

use Behat\Transliterator\Transliterator;
use Ekyna\Bundle\ProductBundle\Entity\Catalog;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Entity\CatalogSlot;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
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
     * @var CatalogRegistry
     */
    protected $registry;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var GeneratorInterface
     */
    protected $pdfGenerator;

    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

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
     * @param CatalogRegistry        $registry
     * @param EngineInterface        $templating
     * @param GeneratorInterface     $pdfGenerator
     * @param SubjectHelperInterface $subjectHelper
     * @param string                 $logoPath
     * @param bool                   $debug
     */
    public function __construct(
        CatalogRegistry $registry,
        EngineInterface $templating,
        GeneratorInterface $pdfGenerator,
        SubjectHelperInterface $subjectHelper,
        $logoPath,
        $debug = false
    ) {
        $this->registry = $registry;
        $this->templating = $templating;
        $this->pdfGenerator = $pdfGenerator;
        $this->subjectHelper = $subjectHelper;
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
        $theme = $this->registry->getTheme($catalog->getTheme())['path'];
        // TODO $template = $catalog->getFormat() === static::FORMAT_EMAIL ? 'email' : 'render';

        if (0 === $catalog->getPages()->count()) {
            $this->buildPages($catalog);
        }

        $content = $this->templating->render('@EkynaProduct/Catalog/render.html.twig', [
            'catalog'   => $catalog,
            'theme'     => $theme,
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
     * Builds the catalog pages from sale items list.
     *
     * @param Catalog $catalog
     *
     * @throws \Ekyna\Component\Commerce\Exception\SubjectException
     */
    private function buildPages(Catalog $catalog)
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
            throw new \LogicException("No product found.");
        }

        $max = $this->registry->getTemplate($catalog->getTemplate())['slots'];
        if (0 >= $max) {
            throw new \LogicException("Template does not have products slots.");
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
     * Returns the catalog response.
     *
     * @param Catalog $catalog
     * @param Request $request
     *
     * @return Response
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
