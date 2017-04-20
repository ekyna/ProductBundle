<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\DataTransformer;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class IdToChoiceObjectTransformer
 * @package Ekyna\Bundle\ProductBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class IdToChoiceObjectTransformer implements DataTransformerInterface
{
    /** @var array<ResourceInterface> */
    private array $choices;

    public function __construct(array $choices)
    {
        $this->choices = $choices;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        if (0 < $value) {
            foreach ($this->choices as $choice) {
                if ($choice->getId() == $value) {
                    return $choice;
                }
            }
            throw new TransformationFailedException('Failed to transform to a choice object.');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (null !== $value) {
            if (in_array($value, $this->choices, true)) {
                return $value->getId();
            }
            throw new TransformationFailedException('Failed to reverse transform the choice object.');
        }

        return null;
    }
}
