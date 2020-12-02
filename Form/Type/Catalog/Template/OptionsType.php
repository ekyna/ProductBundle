<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class OptionsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OptionsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'catalog-page-options');
    }
}
