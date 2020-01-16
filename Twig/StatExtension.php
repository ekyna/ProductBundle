<?php

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
    /**
     * @var ChartRenderer
     */
    private $renderer;


    /**
     * Constructor.
     *
     * @param ChartRenderer $renderer
     */
    public function __construct(ChartRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'product_stat_count_chart',
                [$this->renderer, 'renderProductCountChart'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'product_stat_cross_chart',
                [$this->renderer, 'renderProductCrossChart'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
