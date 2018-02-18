<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Attribute\AttributeTypeRegistryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ProductAttributeValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributeValidator extends ConstraintValidator
{
    /**
     * @var AttributeTypeRegistryInterface
     */
    private $typeRegistry;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;


    /**
     * Constructor.
     *
     * @param AttributeTypeRegistryInterface $typeRegistry
     */
    public function __construct(AttributeTypeRegistryInterface $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * @inheritDoc
     */
    public function validate($productAttribute, Constraint $constraint)
    {
        if (!$productAttribute instanceof Model\ProductAttributeInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\ProductAttributeInterface::class);
        }
        if (!$constraint instanceof ProductAttribute) {
            throw new InvalidArgumentException("Expected instance of " . ProductAttribute::class);
        }

        if (null === $attributeSlot = $productAttribute->getAttributeSlot()) {
            return;
        }

        $type = $this->typeRegistry->getType($attributeSlot->getAttribute()->getType());

        $config = $type->getConstraints($productAttribute);

        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        foreach ($config as $field => $constraints) {
            $violationList = $this
                ->context
                ->getValidator()
                ->validate($this->propertyAccessor->getValue($productAttribute, $field), $constraints);

            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($violationList as $violation) {
                $this->context
                    ->buildViolation($violation->getMessage())
                    ->atPath($field)
                    ->addViolation();
            }
        }
    }
}
