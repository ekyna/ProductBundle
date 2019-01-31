<?php

namespace Ekyna\Bundle\ProductBundle\Service\Stat;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

/**
 * Class AbstractChartBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Stat
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractChartBuilder
{
    protected static $colors = [
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
     * @var CustomerGroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \DatePeriod
     */
    protected $period;

    /**
     * @var ProductInterface
     */
    protected $product;


    /**
     * Constructor.
     *
     * @param CustomerGroupRepositoryInterface $repository
     */
    public function __construct(CustomerGroupRepositoryInterface $repository)
    {
        $this->groupRepository = $repository;
    }

    /**
     * Sets the date period.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return $this|AbstractChartBuilder
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
     * @return $this|AbstractChartBuilder
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
    abstract public function build();

    /**
     * Fills the data by adding missing date indexes.
     *
     * @param array $data
     *
     * @return array
     */
    protected function fillData(array $data)
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
