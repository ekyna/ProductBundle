<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class ChartRenderer
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ChartRenderer
{
    /**
     * @var ChartBuilderFactory
     */
    private $factory;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param ChartBuilderFactory $factory
     * @param EngineInterface     $templating
     * @param array               $config
     */
    public function __construct(ChartBuilderFactory $factory, EngineInterface $templating, array $config)
    {
        $this->factory = $factory;
        $this->templating = $templating;

        $this->config = array_replace([
            'template' => '@EkynaProduct/Admin/Stat/chart.html.twig',
        ], $config);
    }

    /**
     * Renders the product count chart.
     *
     * @param ProductInterface $product
     * @param string           $source
     *
     * @return string
     */
    public function renderProductCountChart(ProductInterface $product, string $source = StatCount::SOURCE_ORDER)
    {
        $config = $this
            ->factory
            ->countChartBuilder()
            ->setProduct($product)
            ->build($source);

        return $this->templating->render($this->config['template'], [
            'id'     => 'product-' . $source . '-count-chart-' . $product->getId(),
            'config' => $config,
        ]);
    }

    /**
     * Renders the product cross chart.
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    public function renderProductCrossChart(ProductInterface $product)
    {
        $config = $this
            ->factory
            ->crossChartBuilder()
            ->setProduct($product)
            ->build();

        return $this->templating->render($this->config['template'], [
            'id'     => 'product-cross-chart-' . $product->getId(),
            'config' => $config,
        ]);
    }
}
