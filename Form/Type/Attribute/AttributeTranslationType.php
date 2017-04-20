<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute;

use Ekyna\Bundle\ProductBundle\Entity\AttributeTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class AttributeTranslationType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => t('field.title', [], 'EkynaUi'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AttributeTranslation::class,
        ]);
    }
}
