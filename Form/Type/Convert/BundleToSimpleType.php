<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleToSimpleType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleToSimpleType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'message' => 'ekyna_product.convert.bundle_to_simple.confirm',
            'attr'               => [
                'class' => 'form-horizontal',
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ConfirmType::class;
    }
}
