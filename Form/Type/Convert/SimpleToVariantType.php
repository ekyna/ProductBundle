<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\Convert;

use Ekyna\Bundle\ProductBundle\Form\Type\ProductAttributesType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\AttributeSetInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function intval;
use function Symfony\Component\Translation\t;

/**
 * Class SimpleToVariantType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\Convert
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SimpleToVariantType extends AbstractType
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent', ProductSearchType::class, [
                'label' => t('product.field.parent', [], 'EkynaProduct'),
                'types' => [ProductTypes::TYPE_VARIABLE],
            ])
            ->add('attributes', ProductAttributesType::class);

        // Post set data
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var ProductInterface $data */
            $data = $event->getData();

            $this->addAttributesForm($event->getForm(), $data->getParent()?->getAttributeSet());
        });

        // Pre submit
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            /** @var ProductInterface $parent */
            $parent = $this->productRepository->find(intval($data['parent']));

            $this->addAttributesForm($event->getForm(), $parent?->getAttributeSet());
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => ProductInterface::class,
            'attr'              => [
                'class' => 'form-horizontal',
            ],
        ]);
    }

    /**
     * Adds the variant form.
     */
    private function addAttributesForm(FormInterface $form, ?AttributeSetInterface $attributeSet): void
    {
        if (!$attributeSet) {
            return;
        }

        $form->add('attributes', ProductAttributesType::class, [
            'attribute_set' => $attributeSet,
        ]);
    }
}
