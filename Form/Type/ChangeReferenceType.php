<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ChangeReferenceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ChangeReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('reference', TextType::class, [
            'label' => t('field.reference', [], 'EkynaUi'),
        ]);
    }
}
