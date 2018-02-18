<?php

namespace Ekyna\Bundle\ProductBundle\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config\BooleanConfigType;
use Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type\BooleanAttributeType;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class BooleanType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BooleanType extends AbstractType
{
    const TRUE  = 'true';
    const FALSE = 'false';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $configDefaults;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     * @param array               $locales
     * @param string              $defaultLocale
     */
    public function __construct(TranslatorInterface $translator, array $locales, $defaultLocale)
    {
        $this->translator = $translator;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritDoc
     */
    public function render(ProductAttributeInterface $productAttribute, $locale = null)
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

    /**
     * @inheritDoc
     */
    public function getConfigDefaults()
    {
        if (null !== $this->configDefaults) {
            return $this->configDefaults;
        }

        $this->configDefaults = [];

        $labels = [
            static::TRUE  => 'ekyna_core.value.yes',
            static::FALSE => 'ekyna_core.value.no',
        ];

        foreach ($labels as $value => $label) {
            $this->configDefaults[$value] = [];
            foreach ($this->locales as $locale) {
                $this->configDefaults[$value][$locale] = $this->translator->trans($label, [], null, $locale);
            }
        }

        return $this->configDefaults;
    }

    /**
     * @inheritDoc
     */
    public function getConfigShowFields(AttributeInterface $attribute)
    {
        $config = $attribute->getConfig();

        $layout = [];

        $values = [
            static::TRUE  => 'ekyna_core.value.yes',
            static::FALSE => 'ekyna_core.value.no',
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
                    'value' => $labels,
                    'type' => 'map',
                    'options' => [
                        'label' => $label,
                    ],
                ];
            }
        }

        return $layout;
    }

    /**
     * @inheritDoc
     */
    public function getConfigType()
    {
        return BooleanConfigType::class;
    }

    /**
     * @inheritDoc
     */
    public function getFormType()
    {
        return BooleanAttributeType::class;
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return 'ekyna_product.attribute.type.boolean';
    }
}