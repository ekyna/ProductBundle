<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type;

use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Component\Commerce\Common\Model\Units;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class UnitAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitAttributeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AttributeInterface $attribute */
        $attribute = $options['attribute'];
        $config = $attribute->getConfig();

        $builder->add('value', NumberType::class, [
            'label' => false,
            'scale' => Units::getPrecision($config['unit']),
            'attr'  => [
                'widget_col'  => 12,
                'input_group' => [
                    'append' => $config['unit'] === Units::PIECE ? $config['suffix'] : Units::getSymbol($config['unit']),
                ],
            ],
        ]);
    }
}