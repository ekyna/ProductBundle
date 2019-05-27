<?php

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class BundleChoiceOptionsTransformer
 * @package Ekyna\Bundle\ProductBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceOptionsTransformer implements DataTransformerInterface
{
    /**
     * @var array
     */
    private $choices;


    /**
     * Constructor.
     *
     * @param array $choices
     */
    public function __construct(array $choices)
    {
        $this->choices = $choices;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        return array_diff($this->choices, (array)$value);
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        return array_diff($this->choices, (array)$value);
    }
}
