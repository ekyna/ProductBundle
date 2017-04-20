<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Form\EventListener\SaleItem\ConfigurableSlotsListener;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Validator\Constraints\SaleItemConfigurable;
use Symfony\Component\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigurableSlotsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableSlotsType extends Form\AbstractType
{
    private ItemBuilder $itemBuilder;

    public function __construct(ItemBuilder $itemBuilder)
    {
        $this->itemBuilder = $itemBuilder;
    }

    public function buildForm(Form\FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new ConfigurableSlotsListener($this->itemBuilder));
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['name'] = $view->vars['full_name'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'         => false,
                'property_path' => 'children',
                'data_class'    => Collection::class,
                'constraints'   => [new SaleItemConfigurable()],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sale_item_configurable_slots';
    }
}
