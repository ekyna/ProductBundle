<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Form\Type\Attribute as Form;
use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Component\Validator\Constraints\Count;

use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SelectType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SelectType extends AbstractType
{
    public function render(ProductAttributeInterface $productAttribute, string $locale = null): ?string
    {
        $labels = array_map(function (AttributeChoiceInterface $choice) use ($locale) {
            return $choice->translate($locale)->getTitle();
        }, $productAttribute->getChoices()->toArray());

        if (!empty($labels)) {
            return implode(' ', $labels);
        }

        return null;
    }

    public function hasChoices(): bool
    {
        return true;
    }

    public function getConstraints(ProductAttributeInterface $productAttribute): array
    {
        if ($productAttribute->getAttributeSlot()->isRequired()) {
            return [
                'choices' => [
                    new Count([
                        'min' => 1,
                    ]),
                ],
            ];
        }

        return [];
    }

    public function getConfigShowFields(AttributeInterface $attribute): array
    {
        $config = $attribute->getConfig();

        return [
            [
                'value'   => $config['multiple'],
                'type'    => 'boolean',
                'options' => [
                    'label' => t('attribute.config.multiple', [], 'EkynaProduct'),
                ],
            ],
        ];
    }

    public function getConfigDefaults(): array
    {
        return [
            'multiple' => true,
        ];
    }

    public function getConfigType(): ?string
    {
        return Form\Config\SelectConfigType::class;
    }

    public function getFormType(): ?String
    {
        return Form\Type\SelectAttributeType::class;
    }

    public function getLabel(): TranslatableInterface
    {
        return t('attribute.type.select', [], 'EkynaProduct');
    }

    public static function getName(): string
    {
        return 'select';
    }
}
