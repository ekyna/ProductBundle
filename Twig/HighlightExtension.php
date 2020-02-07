<?php

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
    /**
     * @inheritdoc
     */
    public function getFunctions()
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
