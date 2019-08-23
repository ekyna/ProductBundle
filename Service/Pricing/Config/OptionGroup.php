<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

/**
 * Class OptionGroup
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroup
{
    /**
     * @var Item[]
     */
    protected $options = [];


    /**
     * Adds the option.
     *
     * @param Item $option
     *
     * @return $this
     */
    public function addOption(Item $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Returns the options.
     *
     * @return Item[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
