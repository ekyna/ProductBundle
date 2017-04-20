<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;

use function ltrim;
use function rtrim;

/**
 * Class CatalogRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Renderer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CatalogHelper
{
    private CatalogRegistry $registry;

    public function __construct(CatalogRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Returns the catalog theme label.
     */
    public function getCatalogThemeLabel(CatalogInterface $catalog): string
    {
        return $this->registry->getTheme($catalog->getTheme())['label'];
    }

    /**
     * Returns the catalog theme stylesheet.
     */
    public function getCatalogThemeStylesheet(CatalogInterface $catalog): string
    {
        return ltrim($this->registry->getTheme($catalog->getTheme())['css'], '/');
    }

    /**
     * Returns the catalog page template label.
     */
    public function getPageTemplateLabel(CatalogPage $page): string
    {
        return $this->registry->getTemplate($page->getTemplate())['label'];
    }

    /**
     * Returns the catalog page template path.
     */
    public function getPageTemplatePath(CatalogPage $page, string $name = 'render'): string
    {
        $config = $this->registry->getTemplate($page->getTemplate());

        return rtrim($config['directory'], '/') . '/' . $name . '.html.twig';
    }
}
