<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Form\Type\SpecialOffer;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupChoiceType;
use Ekyna\Bundle\ProductBundle\Form\Type\Brand\BrandChoiceType;
use Ekyna\Bundle\ProductBundle\Form\Type\ProductSearchType;
use Ekyna\Bundle\ProductBundle\Model\PricingGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SpecialOfferType
 * @package Ekyna\Bundle\ProductBundle\Form\Type\SpecialOffer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('percent', NumberType::class, [
                'label'   => t('common.percent', [], 'EkynaProduct'),
                'decimal' => true,
                'scale'   => 2,
                'attr'    => [
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('minQuantity', NumberType::class, [
                'label'   => t('common.min_quantity', [], 'EkynaProduct'),
                'decimal' => true,
                'scale'   => 3,
                'attr'    => [
                    'min' => 1,
                ],
            ])
            ->add('stack', CheckboxType::class, [
                'label'    => t('special_offer.field.stack', [], 'EkynaProduct'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('startsAt', DateType::class, [
                'label'    => t('field.start_date', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('endsAt', DateType::class, [
                'label'    => t('field.end_date', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('customerGroups', CustomerGroupChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('countries', CountryChoiceType::class, [
                'enabled'  => false,
                'multiple' => true,
                'required' => false,
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var SpecialOfferInterface $specialOffer */
                if (null === $specialOffer = $event->getData()) {
                    return;
                }
                /** @see \Ekyna\Bundle\ProductBundle\EventListener\SpecialOfferListener::onPreUpdate() */
                $specialOffer->takeSnapshot();
            });

        if ($options['product_mode']) {
            return;
        }

        $builder
            ->add('designation', TextType::class, [
                'label'    => t('field.designation', [], 'EkynaUi'),
                'required' => false,
                'help'     => t('leave_blank_to_auto_generate', [], 'EkynaProduct'),
            ])
            ->add('products', ProductSearchType::class, [
                'multiple' => true,
                'required' => false,
                'types'    => [
                    ProductTypes::TYPE_SIMPLE,
                    ProductTypes::TYPE_VARIANT,
                    //TODO (?) ProductTypes::TYPE_BUNDLE,
                ],
            ])
            ->add('pricingGroups', ResourceChoiceType::class, [
                'resource' => PricingGroupInterface::class,
                'multiple' => true,
                'required' => false,
            ])
            ->add('brands', BrandChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'product_mode' => false,
            ])
            ->setAllowedTypes('product_mode', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_product_special_offer';
    }
}
