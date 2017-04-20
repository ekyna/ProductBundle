<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template;

use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SlotsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Catalog\Template
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SlotsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        for ($i = 0; $i < $options['slot_count']; $i++) {
            $this->addSlot($builder, $i);
        }
    }

    /**
     * Creates and adds a slot form.
     *
     * @param FormBuilderInterface $builder
     * @param int                  $index
     * @param bool                 $product
     */
    protected function addSlot(FormBuilderInterface $builder, int $index, bool $product = true): void
    {
        $slot = $builder->create((string)$index, SlotType::class, [
            'product' => $product,
        ]);

        $builder->add($slot);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'catalog-page-slots');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'slot_count'   => 0,
                'by_reference' => false,
            ])
            ->setAllowedTypes('slot_count', 'int');
    }

    public function getBlockPrefix(): ?string
    {
        return 'ekyna_product_catalog_slots';
    }
}
