<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\PriceGridInterface;
use Ekyna\Bundle\ProductBundle\Model\PriceGridRuleInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class PriceGridRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceGridRule extends AbstractResource implements PriceGridRuleInterface
{
    private ?PriceGridInterface $grid = null;
    private int                 $quantity;
    private Decimal             $price;


    public function __construct()
    {
        $this->quantity = 0;
        $this->price = new Decimal(0);
    }

    public function getGrid(): ?PriceGridInterface
    {
        return $this->grid;
    }

    public function setGrid(?PriceGridInterface $grid): PriceGridRuleInterface
    {
        $this->grid = $grid;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): PriceGridRuleInterface
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): Decimal
    {
        return $this->price;
    }

    public function setPrice(Decimal $price): PriceGridRuleInterface
    {
        $this->price = $price;

        return $this;
    }
}
