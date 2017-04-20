<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute;

use Ekyna\Bundle\ProductBundle\Attribute\Type\TypeInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * Class AttributeRenderer
 * @package Ekyna\Bundle\ProductBundle\Attribute
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AttributeRenderer
{
    private AttributeTypeRegistryInterface $typeRegistry;
    private LocaleProviderInterface        $localeProvider;

    /** @var array<string, TranslatableInterface> */
    private ?array $choices = null;

    public function __construct(
        AttributeTypeRegistryInterface $typeRegistry,
        LocaleProviderInterface        $localeProvider
    ) {
        $this->typeRegistry = $typeRegistry;
        $this->localeProvider = $localeProvider;
    }

    /**
     * @param AttributeInterface|string $attribute
     */
    public function getAttributeType($attribute): TypeInterface
    {
        $type = $attribute instanceof AttributeInterface ? $attribute->getType() : $attribute;

        return $this->typeRegistry->getType($type);
    }

    /**
     * @return array<string, TranslatableInterface>
     */
    public function getChoices(): array
    {
        if (null !== $this->choices) {
            return $this->choices;
        }

        $this->choices = [];

        foreach ($this->typeRegistry->getTypes() as $type) {
            $this->choices[$type::getName()] = $type->getLabel();
        }

        return $this->choices;
    }

    /**
     * Renders the product attribute.
     */
    public function renderProductAttribute(ProductAttributeInterface $productAttribute): string
    {
        $attribute = $productAttribute->getAttributeSlot()->getAttribute();

        $type = $this->typeRegistry->getType($attribute->getType());

        return $type->render($productAttribute, $this->localeProvider->getCurrentLocale());
    }
}
