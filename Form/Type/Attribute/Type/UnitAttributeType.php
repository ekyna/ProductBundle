<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type;

use Ekyna\Bundle\CommerceBundle\Model\Units as BUnits;
use Ekyna\Bundle\ProductBundle\Model\AttributeInterface;
use Ekyna\Component\Commerce\Common\Model\Units as CUnits;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UnitAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitAttributeType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = $options['attribute'];
        $config = $attribute->getConfig();

        if ($config['unit'] === CUnits::PIECE) {
            $append = $config['suffix'];
        } else {
            $append = BUnits::getLabel($config['unit'])->trans($this->translator);
        }

        $builder->add('value', NumberType::class, [
            'label' => false,
            'scale' => CUnits::getPrecision($config['unit']),
            'attr'  => [
                'widget_col'  => 12,
                'input_group' => [
                    'append' => $append,
                ],
            ],
        ]);
    }
}
