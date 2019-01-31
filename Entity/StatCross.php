<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class StatCross
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCross
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $date;

    /**
     * @var ProductInterface
     */
    private $source;

    /**
     * @var ProductInterface
     */
    private $target;

    /**
     * @var int
     */
    private $count;

    /**
     * @var CustomerGroupInterface
     */
    private $customerGroup;


    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns the date.
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * Sets the date.
     *
     * @param string $date
     *
     * @return StatCross
     */
    public function setDate(string $date): StatCross
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Returns the source.
     *
     * @return ProductInterface
     */
    public function getSource(): ProductInterface
    {
        return $this->source;
    }

    /**
     * Sets the source.
     *
     * @param ProductInterface $source
     *
     * @return StatCross
     */
    public function setSource(ProductInterface $source): StatCross
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Returns the target.
     *
     * @return ProductInterface
     */
    public function getTarget(): ProductInterface
    {
        return $this->target;
    }

    /**
     * Sets the target.
     *
     * @param ProductInterface $target
     *
     * @return StatCross
     */
    public function setTarget(ProductInterface $target): StatCross
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Returns the count.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Sets the count.
     *
     * @param int $count
     *
     * @return StatCross
     */
    public function setCount(int $count): StatCross
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Returns the customer group.
     *
     * @return CustomerGroupInterface
     */
    public function getCustomerGroup(): CustomerGroupInterface
    {
        return $this->customerGroup;
    }

    /**
     * Sets the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return StatCross
     */
    public function setCustomerGroup(CustomerGroupInterface $group): StatCross
    {
        $this->customerGroup = $group;

        return $this;
    }
}
