<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type\TextAttributeType;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class TextType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TextType extends AbstractType
{
    public function getConstraints(ProductAttributeInterface $productAttribute): array
    {
        if ($productAttribute->getAttributeSlot()->isRequired()) {
            return [
                'value' => [
                    new NotBlank(),
                ],
            ];
        }

        return [];
    }

    public function getFormType(): ?string
    {
        return TextAttributeType::class;
    }

    public function getLabel(): TranslatableInterface
    {
        return t('attribute.type.text', [], 'EkynaProduct');
    }

    public static function getName(): string
    {
        return 'text';
    }
}
