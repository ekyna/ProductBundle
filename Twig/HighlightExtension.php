<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Service\Highlight\Highlight;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class HighlightExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HighlightExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'product_best_sellers',
                [Highlight::class, 'renderBestSellers'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'get_best_sellers',
                [Highlight::class, 'getBestSellers']
            ),
            new TwigFunction(
                'render_cross_selling',
                [Highlight::class, 'renderCrossSelling'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'get_cross_selling',
                [Highlight::class, 'getCrossSelling']
            ),
        ];
    }
}
