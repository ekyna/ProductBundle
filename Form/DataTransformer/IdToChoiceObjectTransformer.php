<?php

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
    /**
     * @var ResourceInterface[]
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
    public function transform($id)
    {
        if (0 < $id) {
            foreach ($this->choices as $choice) {
                if ($choice->getId() == $id) {
                    return $choice;
                }
            }
            //throw new TransformationFailedException('Failed to transform to a choice object.');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($choice)
    {
        if (in_array($choice, $this->choices, true)) {
            return $choice->getId();
        }

        //throw new TransformationFailedException('Failed to reverse transform the choice object.');

        return null;
    }

}
