<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Entity\Catalog;
use Ekyna\Bundle\ProductBundle\Entity\CatalogPage;
use Ekyna\Bundle\ProductBundle\Service\Catalog\CatalogRegistry;

/**
 * Class CatalogExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogExtension extends \Twig_Extension
{
    /**
     * @var CatalogRegistry
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param CatalogRegistry $registry
     */
    public function __construct(CatalogRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('catalog_theme_label',      [$this, 'getCatalogThemeLabel']),
            new \Twig_SimpleFilter('catalog_theme_stylesheet', [$this, 'getCatalogThemeStylesheet']),
            new \Twig_SimpleFilter('catalog_page_template',    [$this, 'getPageTemplate']),
        ];
    }

    /**
     * Returns the catalog label.
     *
     * @param Catalog $catalog
     *
     * @return string
     */
    public function getCatalogThemeLabel(Catalog $catalog)
    {
        return $this->registry->getTheme($catalog->getTheme())['label'];
    }

    /**
     * Returns the catalog theme.
     *
     * @param Catalog $catalog
     *
     * @return string
     */
    public function getCatalogThemeStylesheet(Catalog $catalog)
    {
        return ltrim($this->registry->getTheme($catalog->getTheme())['css'], '/');
    }

    /**
     * Returns the page template.
     *
     * @param CatalogPage $page
     * @param string      $name
     *
     * @return string
     */
    public function getPageTemplate(CatalogPage $page, $name = 'render')
    {
        $config = $this->registry->getTemplate($page->getTemplate());

        return rtrim($config['directory'], '/') . '/' . $name . '.html.twig';
    }
}
