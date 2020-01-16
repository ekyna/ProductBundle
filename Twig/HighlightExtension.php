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
            new TwigFunction(
                'product_best_sellers',
                [$this->service, 'renderBestSellers'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'get_best_sellers',
                [$this, 'getBestSellers']
            ),
            new TwigFunction(
                'render_cross_selling',
                [$this->service, 'renderCrossSelling'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'get_cross_selling',
                [$this, 'getCrossSelling']
            ),
        ];
    }

    public function getBestSellers(array $options = []): array
    {
        $options = array_replace([
            'group'   => null,
            'limit'   => null,
            'from'    => null,
            'id_only' => false,
        ], $options);

        return $this->service->getBestSellers(
            $options['group'],
            $options['limit'],
            $options['from'],
            $options['id_only']
        );
    }

    public function getCrossSelling(array $options = []): array
    {
        $options = array_replace([
            'product' => null,
            'group'   => null,
            'limit'   => null,
            'from'    => null,
            'id_only' => false,
        ], $options);

        return $this->service->getCrossSelling(
            $options['product'],
            $options['group'],
            $options['limit'],
            $options['from'],
            $options['id_only']
        );
    }
}
