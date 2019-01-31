<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Repository\StatCrossRepository;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

/**
 * Class CrossChartBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrossChartBuilder extends AbstractChartBuilder
{
    /**
     * @var StatCrossRepository
     */
    protected $crossRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;


    /**
     * Constructor.
     *
     * @param StatCrossRepository              $crossRepository
     * @param ProductRepositoryInterface       $productRepository
     * @param CustomerGroupRepositoryInterface $groupRepository
     */
    public function __construct(
        StatCrossRepository $crossRepository,
        ProductRepositoryInterface $productRepository,
        CustomerGroupRepositoryInterface $groupRepository
    ) {
        $this->crossRepository = $crossRepository;
        $this->productRepository = $productRepository;

        parent::__construct($groupRepository);
    }

    /**
     * @inheritDoc
     */
    public function build()
    {
        $count = 0;
        $labels = $dataSets = [];

        /** @var \DateTime $d */
        foreach ($this->period as $d) {
            $labels[] = $d->format('Y M');
        }

        // Get best cross selling products for the period
        $targetIds = $this->crossRepository->findBestByProductAndPeriod($this->product, $this->period);

        // By target products
        foreach ($targetIds as $targetId) {
            $target = $this->productRepository->find($targetId);

            $color = self::$colors[$count];
            $dataSets[] = [
                'label'            => $target->getFullDesignation(),
                'fill'             => false,
                'lineTension'      => 0,
                'borderWidth'      => 0.8,
                'borderColor'      => $color,
                'backgroundColor'  => $color,
                'pointStyle'       => 'rectRot',
                'pointRadius'      => 5,
                'pointBorderColor' => 'transparent',
                'data'             => array_values($this->getTargetData($target)),
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
                    'fullWidth' => true,
                    'labels'    => [
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
     * Returns the target product data.
     *
     * @param ProductInterface $target
     *
     * @return array
     */
    public function getTargetData(ProductInterface $target)
    {
        $data = $this
            ->crossRepository
            ->findByProductAndTargetAndPeriod($this->product, $target, $this->period);

        return $this->fillData($data);
    }
}
