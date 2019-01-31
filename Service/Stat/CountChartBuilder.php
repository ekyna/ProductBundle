<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

/**
 * Class CountChartBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountChartBuilder extends AbstractChartBuilder
{
    /**
     * @var StatCountRepository
     */
    protected $countRepository;


    /**
     * Constructor.
     *
     * @param StatCountRepository              $countRepository
     * @param CustomerGroupRepositoryInterface $groupRepository
     */
    public function __construct(StatCountRepository $countRepository, CustomerGroupRepositoryInterface $groupRepository)
    {
        $this->countRepository = $countRepository;

        parent::__construct($groupRepository);
    }

    /**
     * @inheritdoc
     */
    public function build()
    {
        $count = 0;
        $labels = $dataSets = [];

        /** @var \DateTime $d */
        foreach ($this->period as $d) {
            $labels[] = $d->format('Y M');
        }

        // Sum
        $color = self::$colors[$count];
        $dataSets[] = [
            'label'            => 'Tous', // TODO trans
            'fill'             => false,
            'lineTension'      => 0,
            'borderWidth'      => 0.8,
            'borderColor'      => $color,
            'backgroundColor'  => $color,
            'pointStyle'       => 'rectRot',
            'pointRadius'      => 5,
            'pointBorderColor' => 'transparent',
            'data'             => array_values($this->getProductData()),
        ];
        $count++;

        // By group
        /** @var CustomerGroupInterface[] $groups */
        $groups = $this->groupRepository->findAll();
        foreach ($groups as $group) {
            $color = self::$colors[$count];
            $dataSets[] = [
                'label'            => $group->getName(),
                'fill'             => false,
                'lineTension'      => 0,
                'borderWidth'      => 0.8,
                'borderColor'      => $color,
                'backgroundColor'  => $color,
                'pointStyle'       => 'rectRot',
                'pointRadius'      => 5,
                'pointBorderColor' => 'transparent',
                'data'             => array_values($this->getProductData($group)),
            ];
            $count++;
        }

        return [
            'type'    => 'line',
            'data'    => [
                'labels'   => $labels,
                'datasets' => $dataSets,
            ],
            'options' => [
                'legend' => [
                    'labels' => [
                        'usePointStyle' => true,
                    ],
                ],
                'layout' => [
                    'padding' => [
                        'top'    => 12,
                        'bottom' => 12,
                    ],
                ],
                'scales' => [
                    'yAxes' => [
                        [
                            'ticks' => [
                                'suggestedMin' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns the product data for the given group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return array
     */
    private function getProductData(CustomerGroupInterface $group = null)
    {
        $data = $this
            ->countRepository
            ->findByProductAndPeriodAndGroup($this->product, $this->period, $group);

        return $this->fillData($data);
    }
}
