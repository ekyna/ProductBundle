<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\StatCountRepository;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CountChartBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CountChartBuilder
{
    private static $colors = [
        '#f44336',
        '#9c27b0',
        '#3f51b5',
        '#03a9f4',
        '#009688',
        '#8bc34a',
        '#ffeb3b',
        '#ff9800',
        '#795548',
        '#607d8b',
        '#000000',
        '#ff5722',
    ];

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
     * @var \DatePeriod
     */
    private $period;

    /**
     * @var ProductInterface
     */
    private $product;


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
     * Sets the date period.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return CountChartBuilder
     */
    public function setPeriod(\DateTime $from, \DateTime $to)
    {
        $this->period = new \DatePeriod($from, new \DateInterval('P1M'), $to->modify('+1 month'));

        return $this;
    }

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return CountChartBuilder
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Builds the count chart config.
     *
     * @return array
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
            'label'           => 'Tous', // TODO trans
            'fill'            => false,
            'borderWidth'     => 0.8,
            'borderColor'     => $color,
            'backgroundColor' => $color,
            'data'            => array_values($this->getProductData()),
        ];
        $count++;

        // By group
        /** @var CustomerGroupInterface[] $groups */
        $groups = $this->groupRepository->findAll();
        foreach ($groups as $group) {
            $color = self::$colors[$count];
            $dataSets[] = [
                'label'           => $group->getName(),
                'fill'            => false,
                'borderWidth'     => 0.8,
                'borderColor'     => $color,
                'backgroundColor' => $color,
                'data'            => array_values($this->getProductData($group)),
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
                'title'  => [
                    'display' => true,
                    'text'    => $this->translator->trans('ekyna_product.stat.count'),
                ],
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

    /**
     * Fills the data by adding missing date indexes.
     *
     * @param array $data
     *
     * @return array
     */
    private function fillData(array $data)
    {
        /** @var \DateTime $d */
        foreach ($this->period as $d) {
            $index = $d->format('Y-m');
            if (!isset($data[$index])) {
                $data[$index] = 0;
            };
        }
        ksort($data);

        return $data;
    }
}
