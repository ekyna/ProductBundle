<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
use Ekyna\Bundle\ProductBundle\Repository\StatCrossRepository;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ChartBuilderFactory
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ChartBuilderFactory
{
    /**
     * @var StatCountRepository
     */
    private $countRepository;

    /**
     * @var StatCrossRepository
     */
    private $crossRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerGroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param StatCountRepository              $countRepository
     * @param StatCrossRepository              $crossRepository
     * @param ProductRepositoryInterface       $productRepository
     * @param CustomerGroupRepositoryInterface $groupRepository
     * @param TranslatorInterface              $translator
     */
    public function __construct(
        StatCountRepository $countRepository,
        StatCrossRepository $crossRepository,
        ProductRepositoryInterface $productRepository,
        CustomerGroupRepositoryInterface $groupRepository,
        TranslatorInterface $translator
    ) {
        $this->countRepository = $countRepository;
        $this->crossRepository = $crossRepository;
        $this->productRepository = $productRepository;
        $this->groupRepository = $groupRepository;
        $this->translator = $translator;
    }

    /**
     * Returns a new count chart builder.
     *
     * @return CountChartBuilder
     */
    public function countChartBuilder()
    {
        $builder = new CountChartBuilder(
            $this->countRepository,
            $this->groupRepository,
            $this->translator
        );

        $to = new \DateTime();
        $builder->setPeriod((clone $to)->modify('-1 year'), $to);

        return $builder;
    }

    /**
     * Returns a new count cross builder.
     *
     * @return CrossChartBuilder
     */
    public function crossChartBuilder()
    {
        $builder = new CrossChartBuilder(
            $this->crossRepository,
            $this->productRepository,
            $this->groupRepository,
            $this->translator
        );

        $to = new \DateTime();
        $builder->setPeriod((clone $to)->modify('-1 year'), $to);

        return $builder;
    }
}
