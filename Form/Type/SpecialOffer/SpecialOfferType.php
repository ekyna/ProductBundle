<?php

namespace Ekyna\Bundle\ProductBundle\Form\Type\SpecialOffer;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductChoiceType;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SpecialOfferType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SpecialOffer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'attr'     => [
                    'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
                ],
            ])
            ->add('percent', IntegerType::class, [
                'label' => 'ekyna_product.common.percent',
            ])
            ->add('enabled', CheckboxType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('startsAt', DateType::class, [
                'label'    => 'ekyna_core.field.start_date',
                'required' => false,
            ])
            ->add('endsAt', DateType::class, [
                'label'    => 'ekyna_core.field.end_date',
                'required' => false,
            ])
            /*->add('designation', TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_product.leave_blank_to_auto_generate',
                ],
            ])*/
            ->add('products', ProductChoiceType::class, [
                'multiple' => true,
                'required' => false,
                'types'    => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
                ],
            ])
            ->add('brands', BrandChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('groups', CustomerGroupChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('countries', CountryChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface $specialOffer */
                $specialOffer = $event->getData();
                /** @see \Ekyna\Bundle\ProductBundle\EventListener\SpecialOfferEventSubscriber::onPreUpdate() */
                $specialOffer->takeSnapshot();
            });
    }
}
