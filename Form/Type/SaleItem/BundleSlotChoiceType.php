<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleSlotChoiceType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotChoiceType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['id'] = $options['id'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('id')
            ->setAllowedTypes('id', 'string');
    }
}
