<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem\OptionsGroupsListener;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Symfony\Component\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OptionGroupsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupsType extends Form\AbstractType
{
    private ItemBuilder $itemBuilder;

    public function __construct(ItemBuilder $itemBuilder)
    {
        $this->itemBuilder = $itemBuilder;
    }

    public function buildForm(Form\FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new OptionsGroupsListener($this->itemBuilder, $options['exclude_options']));
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['name'] = $view->vars['full_name'];

        // Reverse option groups order
        /** @see \Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem\OptionsGroupsListener::createForms() */
        $view->children = array_reverse($view->children, true);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'         => false,
                'compound'      => true,
                'property_path' => 'children',
                'data_class'    => 'Doctrine\Common\Collections\Collection',
                'exclude_options'        => [],
                'attr'          => [
                    'class' => 'sale-item-options',
                ],
            ])
            ->setAllowedTypes('exclude_options', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'sale_item_option_groups';
    }
}
