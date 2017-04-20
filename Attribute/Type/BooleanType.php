<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config\BooleanConfigType;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type\BooleanAttributeType;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * Class BooleanType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BooleanType extends AbstractType
{
    public const TRUE  = 'true';
    public const FALSE = 'false';

    private TranslatorInterface $translator;
    /** @var string[] */
    private array  $locales;
    private string $defaultLocale;

    private ?array $configDefaults = null;

    public function __construct(TranslatorInterface $translator, array $locales, string $defaultLocale)
    {
        $this->translator = $translator;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    public function render(ProductAttributeInterface $productAttribute, string $locale = null): ?string
    {
        $config = $productAttribute->getAttributeSlot()->getAttribute()->getConfig();
        $value = (bool)$productAttribute->getValue();

        $key = $value ? static::TRUE : static::FALSE;
        if (isset($config[$key])) {
            $labels = $config[$key];

            if (isset($labels[$locale])) {
                return $labels[$locale];
            }

            if ($labels[$this->defaultLocale]) {
                return $labels[$this->defaultLocale];
            }
        }

        return $this->getConfigDefaults()[$key][$this->defaultLocale];
    }

    public function getConfigDefaults(): array
    {
        if (null !== $this->configDefaults) {
            return $this->configDefaults;
        }

        $this->configDefaults = [];

        $labels = [
            static::TRUE  => 'value.yes',
            static::FALSE => 'value.no',
        ];

        foreach ($labels as $value => $label) {
            $this->configDefaults[$value] = [];
            foreach ($this->locales as $locale) {
                $this->configDefaults[$value][$locale] = $this->translator->trans($label, [], 'EkynaUi', $locale);
            }
        }

        return $this->configDefaults;
    }

    public function getConfigShowFields(AttributeInterface $attribute): array
    {
        $config = $attribute->getConfig();

        $layout = [];

        $values = [
            static::TRUE  => t('value.yes', [], 'EkynaUi'),
            static::FALSE => t('value.no', [], 'EkynaUi'),
        ];

        foreach ($values as $value => $label) {
            $labels = [];

            if (isset($config[$value])) {
                foreach ($this->locales as $locale) {
                    if (isset($config[$value][$locale])) {
                        $labels[strtoupper($locale)] = $config[$value][$locale];
                    }
                }
            }

            if (!empty($labels)) {
                $layout[] = [
                    'value'   => $labels,
                    'type'    => 'map',
                    'options' => [
                        'label' => $label,
                    ],
                ];
            }
        }

        return $layout;
    }

    public function getConfigType(): ?string
    {
        return BooleanConfigType::class;
    }

    public function getFormType(): ?string
    {
        return BooleanAttributeType::class;
    }

    public function getLabel(): TranslatableInterface
    {
        return t('attribute.type.boolean', [], 'EkynaProduct');
    }

    public static function getName(): string
    {
        return 'boolean';
    }
}
