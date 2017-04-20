<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config;

use Ekyna\Bundle\CommerceBundle\Model\Units;
use Ekyna\Bundle\ProductBundle\Validator\Constraints\UnitAttributeConfig;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class UnitConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('unit', ConstantChoiceType::class, [
                'label' => t('unit.label', [], 'EkynaCommerce'),
                'class' => Units::class,
            ])
            ->add('suffix', Type\TextType::class, [
                'label'    => t('attribute.config.suffix', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'help_text' => t('attribute.help.suffix', [], 'EkynaProduct'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('constraints', [
            new UnitAttributeConfig(),
        ]);
    }
}
