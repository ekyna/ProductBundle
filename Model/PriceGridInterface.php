<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class PriceGrid
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PriceGridInterface extends ResourceInterface
{
    public function getGroup(): ?CustomerGroupInterface;

    public function setGroup(?CustomerGroupInterface $group): PriceGridInterface;

    /**
     * @return Collection<PriceGridRuleInterface>
     */
    public function getRules(): Collection;

    public function hasRule(PriceGridRuleInterface $rule): bool;

    public function addRule(PriceGridRuleInterface $rule): PriceGridInterface;

    public function removeRule(PriceGridRuleInterface $rule): PriceGridInterface;
}
