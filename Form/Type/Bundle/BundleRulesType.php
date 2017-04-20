<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class BundleRulesType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRulesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'           => t('bundle_rule.label.plural', [], 'EkynaProduct'),
            'required'        => false,
            'sub_widget_col'  => 11,
            'button_col'      => 1,
            'allow_sort'      => true,
            'add_button_text' => t('bundle_rule.button.add', [], 'EkynaProduct'),
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'product-bundle-rules');
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
