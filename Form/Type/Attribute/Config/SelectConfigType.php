<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SelectConfigType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Attribute\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SelectConfigType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('multiple', CheckboxType::class, [
            'label' => 'ekyna_product.attribute.config.multiple',
            'required' => false,
        ]);
    }
}