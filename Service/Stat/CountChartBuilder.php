<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Entity\StatCount;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface as Group;
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
     * @inheritDoc
     */
    public function build(string $source = null)
    {
        StatCount::isValidSource($source);

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
            'data'             => array_values($this->getProductData($source)),
        ];
        $count++;

        // By group
        /** @var Group[] $groups */
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
                'data'             => array_values($this->getProductData($source, $group)),
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
     * @param string $source
     * @param Group  $group
     *
     * @return array
     */
    private function getProductData(string $source, Group $group = null)
    {
        $data = $this
            ->countRepository
            ->findByProductAndPeriodAndGroup($this->product, $source, $this->period, $group);

        return $this->fillData($data);
    }
}
