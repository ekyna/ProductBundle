<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

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
     *
     * @return string
     */
    public function renderProductCountChart(ProductInterface $product)
    {
        $config = $this
            ->factory
            ->countChartBuilder()
            ->setProduct($product)
            ->build();

        return $this->templating->render($this->config['template'], [
            'id'     => 'product-count-chart-' . $product->getId(),
            'config' => $config,
        ]);
    }
}
