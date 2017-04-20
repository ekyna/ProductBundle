<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Twig\Environment;

/**
 * Class ChartRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ChartRenderer
{
    private ChartBuilderFactory $factory;
    private Environment         $twig;
    private array               $config;

    public function __construct(ChartBuilderFactory $factory, Environment $twig, array $config)
    {
        $this->factory = $factory;
        $this->twig = $twig;

        $this->config = array_replace([
            'template' => '@EkynaProduct/Admin/Stat/chart.html.twig',
        ], $config);
    }

    /**
     * Renders the product count chart.
     */
    public function renderProductCountChart(ProductInterface $product, string $source = StatCount::SOURCE_ORDER): string
    {
        $config = $this
            ->factory
            ->countChartBuilder()
            ->setProduct($product)
            ->build($source);

        return $this->twig->render($this->config['template'], [
            'id'     => 'product-' . $source . '-count-chart-' . $product->getId(),
            'config' => $config,
        ]);
    }

    /**
     * Renders the product cross chart.
     */
    public function renderProductCrossChart(ProductInterface $product): string
    {
        $config = $this
            ->factory
            ->crossChartBuilder()
            ->setProduct($product)
            ->build();

        return $this->twig->render($this->config['template'], [
            'id'     => 'product-cross-chart-' . $product->getId(),
            'config' => $config,
        ]);
    }
}
