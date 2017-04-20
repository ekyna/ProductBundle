<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
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
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'catalog-page-options');
    }
}
