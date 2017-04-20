<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class CatalogExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogExtension extends AbstractExtension
{
    public function getFilters():array
    {
        return [
            new TwigFilter('catalog_theme_label',         [CatalogHelper::class, 'getCatalogThemeLabel']),
            new TwigFilter('catalog_theme_stylesheet',    [CatalogHelper::class, 'getCatalogThemeStylesheet']),
            new TwigFilter('catalog_page_template_label', [CatalogHelper::class, 'getPageTemplateLabel']),
            new TwigFilter('catalog_page_template_path',  [CatalogHelper::class, 'getPageTemplatePath']),
        ];
    }
}
