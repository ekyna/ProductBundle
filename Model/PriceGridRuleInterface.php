<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class PriceGridRule
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PriceGridRuleInterface extends ResourceInterface
{
    public function getGrid(): ?PriceGridInterface;

    public function setGrid(?PriceGridInterface $grid): PriceGridRuleInterface;

    public function getQuantity(): int;

    public function setQuantity(int $quantity): PriceGridRuleInterface;

    public function getPrice(): Decimal;

    public function setPrice(Decimal $price): PriceGridRuleInterface;
}
