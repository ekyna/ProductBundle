<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Symfony\Component\Form\AbstractType;

/**
 * Class CoverType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CoverType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return OptionsType::class;
    }
}
