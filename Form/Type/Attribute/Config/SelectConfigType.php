<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SelectConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SelectConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('multiple', CheckboxType::class, [
            'label' => t('attribute.config.multiple', [], 'EkynaProduct'),
            'required' => false,
        ]);
    }
}
