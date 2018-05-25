<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SlotsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SlotsType extends AbstractType
{
    /**
     * Creates and adds a slot form.
     *
     * @param FormBuilderInterface $builder
     * @param int                  $index
     * @param bool                 $product
     *
     * @return FormBuilderInterface
     */
    protected function addSlot(FormBuilderInterface $builder, $index, $product = true)
    {
        $slot = $builder
            ->create((string)$index, SlotType::class, [
                'product' => $product,
            ]);

        $builder->add($slot);

        return $slot;
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'catalog-page-slots');
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_catalog_slots';
    }
}
