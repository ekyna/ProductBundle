<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OptionGroupsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupsType extends Form\AbstractType
{
    /**
     * @var ProductProvider
     */
    private $productProvider;


    /**
     * Constructor.
     *
     * @param ProductProvider $productProvider
     */
    public function __construct(ProductProvider $productProvider)
    {
        $this->productProvider = $productProvider;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) {
                /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
                $item = $event->getForm()->getParent()->getData();
                $product = $this->productProvider->resolve($item);

                $form = $event->getForm();

                foreach ($product->getOptionGroups() as $optionGroup) {
                    foreach ($item->getChildren() as $index => $child) {
                        if ($optionGroup->getId() === $child->getData(ItemBuilder::OPTION_GROUP_ID)) {
                            $form->add('option_group_' . $optionGroup->getId(), OptionGroupType::class, [
                                'label'         => $optionGroup->getTitle(),
                                'property_path' => '[' . $index . ']',
                                'option_group'  => $optionGroup,
                            ]);

                            continue 2;
                        }
                    }

                    throw new LogicException(sprintf(
                        "Sale item was not found for option group #%s.\n" .
                        "You must call ItemBuilder::initializeItem() first.",
                        $optionGroup->getId()
                    ));
                }
            });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'         => false,
                'property_path' => 'children',
                'data_class'    => 'Doctrine\Common\Collections\Collection',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_product_sale_item_option_groups';
    }
}
