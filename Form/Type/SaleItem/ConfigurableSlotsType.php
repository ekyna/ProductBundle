<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SaleItem;

use Ekyna\Bundle\ProductBundle\Exception\LogicException;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigurableSlotsType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SaleItem
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableSlotsType extends Form\AbstractType
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
     * @inheritdoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(Form\FormEvents::PRE_SET_DATA, function (Form\FormEvent $event) {
                /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
                $item = $event->getForm()->getParent()->getData();
                $product = $this->productProvider->resolve($item);

                $form = $event->getForm();

                foreach ($product->getBundleSlots() as $bundleSlot) {
                    foreach ($item->getChildren() as $index => $child) {
                        $bundleSlotId = intval($child->getData(ItemBuilder::BUNDLE_SLOT_ID));
                        if ($bundleSlotId == $bundleSlot->getId()) {
                            $form->add('slot_' . $bundleSlot->getId(), ConfigurableSlotType::class, [
                                'bundle_slot'   => $bundleSlot,
                                'property_path' => '[' . $index . ']',
                            ]);
                            continue 2;
                        }
                    }


                    throw new LogicException(sprintf(
                        "Sale item was not found for bundle slot #%s.\n" .
                        "You must call ItemBuilder::initializeItem() first.",
                        $bundleSlot->getId()
                    ));
                }
            })
            ->addEventListener(Form\FormEvents::POST_SUBMIT, function (Form\FormEvent $event) {
                /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
                $item = $event->getForm()->getParent()->getData();
                $product = $this->productProvider->resolve($item);

                $this
                    ->productProvider
                    ->getItemBuilder()
                    ->buildItemFromProduct($item, $product);

                $event->setData($item);
            }, 2048);
    }

    /**
     * @inheritdoc
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
        return 'ekyna_product_configurable_slots';
    }
}
