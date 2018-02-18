<?php

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

/**
 * Class UnitType
 * @package Ekyna\Bundle\ProductBundle\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function render(ProductAttributeInterface $productAttribute, $locale = null)
    {
        $config = $productAttribute->getAttributeSlot()->getAttribute()->getConfig();

        if (empty($value = $productAttribute->getValue())) {
            return null;
        }

        $formatter = NumberFormatter::create($locale, NumberFormatter::DECIMAL);

        return sprintf(
            '%s %s',
            $formatter->format($value, NumberFormatter::TYPE_DEFAULT),
            $config['unit'] === CUnits::PIECE ? $config['suffix'] : CUnits::getSymbol($config['unit'])
        );
    }

    /**
     * @inheritDoc
     */
    public function getConstraints(ProductAttributeInterface $productAttribute)
    {
        return [
            'value' => [
                new NotBlank(),
                new Type('numeric'),
                new GreaterThan(['value' => 0]),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getConfigShowFields(AttributeInterface $attribute)
    {
        $config = $attribute->getConfig();

        return [
            [
                'value'   => BUnits::getLabel($config['unit']),
                'type'    => 'text',
                'options' => [
                    'label'        => 'ekyna_commerce.unit.label',
                    'trans_domain' => null,
                ],
            ],
            [
                'value'   => $config['suffix'],
                'type'    => 'text',
                'options' => [
                    'label' => 'ekyna_product.attribute.config.suffix',
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getConfigDefaults()
    {
        return [
            'unit'   => CUnits::PIECE,
            'suffix' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getConfigType()
    {
        return UnitConfigType::class;
    }

    /**
     * @inheritDoc
     */
    public function getFormType()
    {
        return UnitAttributeType::class;
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return 'ekyna_product.attribute.type.unit';
    }
}