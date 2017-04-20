<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

/**
 * Class OptionGroup
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroup
{
    /** @var array<Item> */
    protected array $options = [];

    public function addOption(Item $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * @return array<Item>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
