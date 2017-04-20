<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class VariantValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantValidator extends ConstraintValidator
{
    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;


    /**
     * Constructor.
     *
     * @param LocaleProviderInterface $localeProvider
     */
    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * @inheritDoc
     */
    public function validate($variant, Constraint $constraint)
    {
        if (!$variant instanceof Model\ProductInterface) {
            throw new InvalidArgumentException("Expected instance of ProductInterface");
        }
        if (!$constraint instanceof Variant) {
            throw new InvalidArgumentException("Expected instance of Variant (validation constraint)");
        }

        /* @var Model\ProductInterface $variant */
        /* @var Variant $constraint */

        // Asserts the constraint is applied to a variant.
        Model\ProductTypes::assertVariant($variant);

        $this->validateAttributes($variant, $constraint);
    }

    /**
     * Validates the variant attributes regarding to parent attribute set.
     *
     * @param Model\ProductInterface $variant
     * @param Variant                $constraint
     */
    protected function validateAttributes(Model\ProductInterface $variant, Variant $constraint)
    {
        // Parent is mandatory
        if (null === $parent = $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }

        // Parent attribute set is mandatory
        if (null === $attributeSet = $parent->getAttributeSet()) {
            if ($this->context->getGroup() === 'convert_variant') {
                return;
            }

            throw new RuntimeException("Variant's parent attribute set must be defined.");
        }

        // If no attributes (and no required slots), attributesDesignation and attributesTitle (translations)
        // can't be auto-generated, so we need the user to provide them
        if (!$attributeSet->hasRequiredSlot() && 0 == $variant->getAttributes()->count()) {
            // Designation
            if (empty($variant->getDesignation())) {
                $this->context
                    ->buildViolation($constraint->designationNeeded)
                    ->atPath('designation')
                    ->addViolation();
            }

            // Translations title
            foreach ($this->localeProvider->getAvailableLocales() as $locale) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\ProductTranslationInterface $translation */
                if (null !== $translation = $variant->getTranslations()->get($locale)) {
                    if (empty($translation->getTitle())) {
                        $this->context
                            ->buildViolation($constraint->translationTitleNeeded)
                            ->atPath('translations['. $locale . '].title')
                            ->addViolation();
                    }
                } elseif ($locale == $this->localeProvider->getFallbackLocale()) {
                    $this->context
                        ->buildViolation($constraint->translationTitleNeeded)
                        ->atPath('translations['. $locale . '].title')
                        ->addViolation();
                }
            }
        }

        // Asserts that the variant is unique (ie no other parent's variant has the same attributes collection)
        $signature = $variant->getUniquenessSignature();
        $variants = $parent->getVariants();
        foreach ($variants as $v) {
            if ($v !== $variant && $v->getUniquenessSignature() === $signature) {
                $this->context
                    ->buildViolation($constraint->variantIsNotUnique)
                    ->atPath('attributes')
                    ->addViolation();

                return;
            }
        }
    }
}
