<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
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
     * @param CustomerGroupRepositoryInterface $groupRepository
     * @param TranslatorInterface              $translator
     */
    public function __construct(
        StatCountRepository $countRepository,
        CustomerGroupRepositoryInterface $groupRepository,
        TranslatorInterface $translator
    ) {
        $this->countRepository = $countRepository;
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
}
