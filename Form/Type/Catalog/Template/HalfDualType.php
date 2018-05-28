<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class HalfDualType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class HalfDualType extends SlotsType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addSlot($builder, 0);
        $this->addSlot($builder, 1);
        $this->addSlot($builder, 2);
    }
}
