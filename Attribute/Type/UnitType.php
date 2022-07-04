<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\CommerceBundle\Model\Units as BUnits;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config\UnitConfigType;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type\UnitAttributeType;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Ekyna\Component\Commerce\Common\Model\Units as CUnits;
use NumberFormatter;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function floatval;
use function sprintf;
use function str_contains;
use function Symfony\Component\Translation\t;

/**
 * Class UnitType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function render(ProductAttributeInterface $productAttribute, string $locale = null): ?string
    {
        $config = $productAttribute->getAttributeSlot()->getAttribute()->getConfig();

        if (empty($value = $productAttribute->getValue())) {
            return null;
        }

        $value = str_contains($value, '.') ? floatval($value) : intval($value);

        $formatter = NumberFormatter::create($locale, NumberFormatter::DECIMAL);

        if ($config['unit'] === CUnits::PIECE) {
            return sprintf('%s %s', $formatter->format($value, NumberFormatter::TYPE_DEFAULT), $config['suffix']);
        }

        if (BUnits::hasTranslatableFormat($config['unit'])) {
            $format = $this->translator->trans(
                BUnits::getFormat($config['unit']), [], BUnits::getTranslationDomain(), $locale
            );
        } else {
            $format = BUnits::getFormat($config['unit']);
        }

        return sprintf($format, $formatter->format($value, NumberFormatter::TYPE_DEFAULT));
    }

    public function getConstraints(ProductAttributeInterface $productAttribute): array
    {
        return [
            'value' => [
                new NotBlank(),
                new Type('numeric'),
                new GreaterThan(['value' => 0]),
            ],
        ];
    }

    public function getConfigShowFields(AttributeInterface $attribute): array
    {
        $config = $attribute->getConfig();

        return [
            [
                'value'   => BUnits::getLabel($config['unit']),
                'type'    => 'text',
                'options' => [
                    'label'        => t('unit.label', [], 'EkynaCommerce'),
                    'trans_domain' => null,
                ],
            ],
            [
                'value'   => $config['suffix'],
                'type'    => 'text',
                'options' => [
                    'label' => t('attribute.config.suffix', [], 'EkynaProduct'),
                ],
            ],
        ];
    }

    public function getConfigDefaults(): array
    {
        return [
            'unit'   => CUnits::PIECE,
            'suffix' => null,
        ];
    }

    public function getConfigType(): ?string
    {
        return UnitConfigType::class;
    }

    public function getFormType(): ?string
    {
        return UnitAttributeType::class;
    }

    public function getLabel(): TranslatableInterface
    {
        return t('attribute.type.unit', [], 'EkynaProduct');
    }

    public static function getName(): string
    {
        return 'unit';
    }
}
