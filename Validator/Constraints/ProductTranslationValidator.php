<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductTranslationRepositoryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ProductTranslationValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTranslationValidator extends ConstraintValidator
{
    /**
     * @var ProductTranslationRepositoryInterface
     */
    private $repository;


    /**
     * Constructor.
     *
     * @param ProductTranslationRepositoryInterface $repository
     */
    public function __construct(ProductTranslationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function validate($translation, Constraint $constraint)
    {
        if (!$translation instanceof Model\ProductTranslationInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\ProductTranslationInterface::class);
        }
        if (!$constraint instanceof ProductTranslation) {
            throw new InvalidArgumentException("Expected instance of " . ProductTranslation::class);
        }

        $this->validateTitle($translation);
    }

    /**
     * Validates the product translation title uniqueness.
     *
     * @param Model\ProductTranslationInterface $translation
     */
    private function validateTitle(Model\ProductTranslationInterface $translation)
    {
        if (null === $duplicate = $this->repository->findDuplicate($translation)) {
            return;
        }

        $this
            ->context
            ->buildViolation('ekyna_product.product.duplicate_translation', [
                '%reference%' => $duplicate->getTranslatable()->getReference(),
            ])
            ->atPath('title')
            ->addViolation();
    }
}
