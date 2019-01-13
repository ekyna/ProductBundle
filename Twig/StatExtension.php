<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Service\Stat\ChartRenderer;

/**
 * Class StatExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction(
                'product_stat_count_chart',
                [$this->renderer, 'renderProductCountChart'],
                ['is_safe' => ['html']]
            )
        ];
    }
}
