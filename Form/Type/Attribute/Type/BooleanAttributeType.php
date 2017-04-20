<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class BooleanAttributeType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BooleanAttributeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', ChoiceType::class, [
            'label'                     => false,
            'choices'                   => [
                'value.yes' => '1',
                'value.no'  => '0',
            ],
            'choice_translation_domain' => 'EkynaUi',
            'expanded'                  => true,
            'required'                  => $options['required'],
            'placeholder'               => !$options['required'] ? t('value.undefined', [], 'EkynaUi') : null,
            'attr'                      => [
                'class' => 'inline',
            ],
        ]);
    }
}
