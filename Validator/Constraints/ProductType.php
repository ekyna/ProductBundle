<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Class ProductType
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends Constraint
{
    public $invalidProductType = 'ekyna_product.invalid_product_type';

    /**
     * @var array
     */
    public $types;


    /**
     * @inheritDoc
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            if (!is_array($options)) {
                $options = [$options];
            }
            if (!array_key_exists('types', $options)) {
                $options = [
                    'types' => $options,
                ];
            }
            foreach ($options['types'] as $type) {
                if (!ProductTypes::isValidType($type)) {
                    throw new InvalidOptionsException("Type '$type' is invalid.", ['types']);
                }
            }
        }

        parent::__construct($options);

        if (null === $this->types) {
            throw new MissingOptionsException(
                sprintf('Option "types" must be given for constraint %s', __CLASS__),
                ['types']
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getRequiredOptions()
    {
        return ['types'];
    }
}
