<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class BestSeller
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatCount
{
    /* Warning : StatCalculator uses these constant values to build queries */
    public const SOURCE_ORDER = 'order';
    public const SOURCE_QUOTE = 'quote';

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var string
     */
    private $source;

    /***
     * @var string
     */
    private $date;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var CustomerGroupInterface
     */
    private $customerGroup;

    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * Returns the sources.
     *
     * @return string[]
     */
    public static function getSources(): array
    {
        return [self::SOURCE_ORDER, self::SOURCE_QUOTE];
    }

    /**
     * Validates the given source.
     *
     * @param string $source
     */
    public static function isValidSource(string $source)
    {
        if (!in_array($source, self::getSources(), true)) {
            throw new InvalidArgumentException("Invalid stat source.");
        }
    }

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
     * Returns the source.
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Sets the source.
     *
     * @param string $source
     *
     * @return StatCount
     */
    public function setSource(string $source): StatCount
    {
        $this->source = $source;

        return $this;
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
     * @return StatCount
     */
    public function setDate(string $date): StatCount
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return StatCount
     */
    public function setProduct(ProductInterface $product): StatCount
    {
        $this->product = $product;

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
     * @return StatCount
     */
    public function setCount(int $count): StatCount
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Returns the customer group.
     *
     * @return CustomerGroupInterface|null
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
     * @return StatCount
     */
    public function setCustomerGroup(CustomerGroupInterface $group): StatCount
    {
        $this->customerGroup = $group;

        return $this;
    }

    /**
     * Returns the updatedAt.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Sets the "updated at" date time.
     *
     * @param \DateTime $updatedAt
     *
     * @return StatCount
     */
    public function setUpdatedAt(\DateTime $updatedAt): StatCount
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
