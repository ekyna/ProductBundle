<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Bundle;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BundleRulesType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Bundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRulesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'           => 'ekyna_product.bundle_rule.label.plural',
            'required'        => false,
            'sub_widget_col'  => 11,
            'button_col'      => 1,
            'allow_sort'      => true,
            'add_button_text' => 'ekyna_product.bundle_rule.button.add',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'product-bundle-rules');
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
