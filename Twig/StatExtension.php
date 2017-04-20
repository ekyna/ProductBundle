<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Service\Stat\ChartRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class StatExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'product_stat_count_chart',
                [ChartRenderer::class, 'renderProductCountChart'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'product_stat_cross_chart',
                [ChartRenderer::class, 'renderProductCrossChart'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
