<?php

namespace Ekyna\Bundle\ProductBundle\Twig;

use Ekyna\Bundle\ProductBundle\Service\Highlight\Highlight;

/**
 * Class HighlightExtension
 * @package Ekyna\Bundle\ProductBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HighlightExtension extends \Twig_Extension
{
    /**
     * @var Highlight
     */
    private $service;


    /**
     * Constructor.
     *
     * @param Highlight $service
     */
    public function __construct(Highlight $service)
    {
        $this->service = $service;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'product_best_sellers',
                [$this->service, 'renderBestSellers'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'get_best_sellers',
                [$this->service, 'getBestSellers']
            ),
            new \Twig_SimpleFunction(
                'render_cross_selling',
                [$this->service, 'renderCrossSelling'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'get_cross_selling',
                [$this->service, 'getCrossSelling']
            ),
        ];
    }
}
